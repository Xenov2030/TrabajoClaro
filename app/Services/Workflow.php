<?php

namespace App\Services;

use App\Models\ActivityEvent;
use App\Models\Business;
use App\Models\Customer;
use App\Models\Job;
use App\Models\PaymentEntry;
use App\Models\ProductEvent;
use App\Models\Quote;
use App\Models\QuoteDecision;
use App\Models\QuoteVersion;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class Workflow
{
    public function activity(int $business, string $name, string $type, int $id, array $details = []): void
    {
        ActivityEvent::create(['business_id' => $business, 'name' => $name, 'subject_type' => $type, 'subject_id' => $id, 'details' => $details]);
    }

    public function metric(int $business, string $name): void
    {
        if (Business::findOrFail($business)->is_demo) {
            return;
        }
        if (str_starts_with($name, 'first_') || $name === 'registration_completed') {
            ProductEvent::firstOrCreate(['business_id' => $business, 'dedupe_key' => $name], ['name' => $name]);
        } else {
            ProductEvent::create(['business_id' => $business, 'name' => $name]);
        }
    }

    public function createQuote(int $business, int $customer, array $draft): Quote
    {
        return DB::transaction(function () use ($business, $customer, $draft) {
            Customer::where('business_id', $business)->whereNull('archived_at')->findOrFail($customer);
            $b = Business::lockForUpdate()->findOrFail($business);
            $q = Quote::create(['business_id' => $business, 'customer_id' => $customer, 'number' => $b->next_quote, 'draft' => Money::quote($draft)]);
            $b->increment('next_quote');
            $this->activity($business, 'Presupuesto creado', 'quote', $q->id);
            $this->metric($business, 'first_quote_created');

            return $q;
        });
    }

    public function publish(Quote $quote): array
    {
        return DB::transaction(function () use ($quote) {
            $q = Quote::where('business_id', $quote->business_id)->lockForUpdate()->findOrFail($quote->id);
            if ($q->status === 'aceptado') {
                $this->fail('Un presupuesto aceptado se conserva. Duplicalo para una nueva propuesta.');
            }
            $b = Business::findOrFail($q->business_id);
            if ($q->draft['expires_on'] < now($b->timezone)->toDateString()) {
                $this->fail('Revisá el vencimiento antes de publicar.');
            }
            $customer = Customer::where('business_id', $b->id)->findOrFail($q->customer_id);
            QuoteVersion::where('quote_id', $q->id)->whereNull('revoked_at')->update(['revoked_at' => now(), 'status' => 'sustituido']);
            $token = bin2hex(random_bytes(32));
            $v = QuoteVersion::create(['business_id' => $b->id, 'quote_id' => $q->id, 'version' => (int) QuoteVersion::where('quote_id', $q->id)->max('version') + 1, 'snapshot' => array_merge($q->draft, ['number' => $q->number, 'business' => ['name' => $b->name, 'timezone' => $b->timezone], 'customer' => ['name' => $customer->name, 'address' => $customer->address]]), 'token_hash' => hash('sha256', $token), 'expires_on' => $q->draft['expires_on']]);
            $q->update(['status' => 'pendiente']);
            $this->activity($b->id, 'Versión publicada', 'quote', $q->id, ['version' => $v->version]);
            $this->metric($b->id, 'first_version_published');

            return [$v, $token];
        });
    }

    public function decide(QuoteVersion $version, array $data): void
    {
        DB::transaction(function () use ($version, $data) {
            // Siempre bloquear presupuesto antes de versión: mismo orden que publicar y revocar.
            $q = Quote::lockForUpdate()->findOrFail($version->quote_id);
            $v = QuoteVersion::lockForUpdate()->findOrFail($version->id);
            $this->assertAvailable($v);
            if (QuoteDecision::where('quote_version_id', $v->id)->exists()) {
                $this->fail('Esta versión ya tiene una decisión.');
            }
            if (! in_array($data['decision'], ['aceptado', 'rechazado', 'modificacion'], true) || empty($data['confirmed']) || trim($data['name']) === '') {
                $this->fail('Confirmá la decisión e indicá tu nombre.');
            }
            QuoteDecision::create(['business_id' => $v->business_id, 'quote_version_id' => $v->id, 'decision' => $data['decision'], 'name' => $data['name'], 'message' => $data['message'] ?? null]);
            $v->update(['status' => $data['decision']]);
            $q->update(['status' => $data['decision']]);
            $this->activity($v->business_id, 'Decisión registrada', 'quote', $q->id, ['decision' => $data['decision'], 'version' => $v->version]);
            if ($data['decision'] === 'aceptado') {
                $this->metric($v->business_id, 'quote_accepted');
            }
        });
    }

    public function assertAvailable(QuoteVersion $v): void
    {
        if ($v->revoked_at || $v->status !== 'pendiente' || $v->expires_on < now($v->snapshot['business']['timezone'])->toDateString()) {
            $this->fail('Esta versión ya no admite decisiones: fue respondida, venció o se revocó.');
        }
    }

    public function convert(Quote $quote): Job
    {
        return DB::transaction(function () use ($quote) {
            $q = Quote::where('business_id', $quote->business_id)->lockForUpdate()->findOrFail($quote->id);
            if ($existing = Job::where('business_id', $q->business_id)->where('quote_id', $q->id)->first()) {
                return $existing;
            }
            if ($q->status !== 'aceptado') {
                $this->fail('Primero necesitás un presupuesto aceptado.');
            }
            $v = QuoteVersion::where('quote_id', $q->id)->where('status', 'aceptado')->firstOrFail();
            $s = $v->snapshot;
            $j = Job::create(['business_id' => $q->business_id, 'customer_id' => $q->customer_id, 'quote_id' => $q->id, 'title' => $s['title'], 'scope' => $s['scope'], 'currency' => $s['currency'], 'amount_minor' => $s['total_minor']]);
            $this->activity($j->business_id, 'Trabajo creado', 'job', $j->id);
            $this->metric($j->business_id, 'job_created');

            return $j;
        });
    }

    public function pay(Job $job, array $data): PaymentEntry
    {
        return DB::transaction(function () use ($job, $data) {
            $j = Job::where('business_id', $job->business_id)->lockForUpdate()->findOrFail($job->id);
            $minor = Money::minor((string) $data['amount']);
            if ($existing = PaymentEntry::where('business_id', $j->business_id)->where('idempotency_key', $data['idempotency_key'])->first()) {
                if ($existing->job_id !== $j->id || (int) $existing->amount_minor !== $minor || $existing->currency !== $data['currency'] || $existing->paid_on !== $data['paid_on'] || $existing->method !== $data['method'] || ($existing->reference ?? '') !== ($data['reference'] ?? '')) {
                    $this->fail('La clave de reintento ya se usó para otro movimiento.');
                }

                return $existing;
            }
            if ($data['currency'] !== $j->currency || $minor <= 0 || $minor > $j->balance()) {
                $this->fail('La moneda debe coincidir y el importe debe ser mayor a cero sin superar el saldo. No se admiten sobrepagos.');
            }
            $p = PaymentEntry::create(['business_id' => $j->business_id, 'customer_id' => $j->customer_id, 'job_id' => $j->id, 'amount_minor' => $minor, 'currency' => $j->currency, 'paid_on' => $data['paid_on'], 'method' => $data['method'], 'reference' => $data['reference'] ?? null, 'idempotency_key' => $data['idempotency_key']]);
            $this->activity($j->business_id, 'Cobro registrado', 'job', $j->id, ['payment_id' => $p->id]);
            $this->metric($j->business_id, 'first_payment_recorded');

            return $p;
        });
    }

    public function void(PaymentEntry $payment, string $reason): void
    {
        DB::transaction(function () use ($payment, $reason) {
            Job::where('business_id', $payment->business_id)->lockForUpdate()->findOrFail($payment->job_id);
            $p = PaymentEntry::where('business_id', $payment->business_id)->lockForUpdate()->findOrFail($payment->id);
            if (trim($reason) === '') {
                $this->fail('Indicá el motivo de anulación.');
            }
            if (! $p->voided_at) {
                $p->update(['voided_at' => now(), 'void_reason' => $reason]);
                $this->activity($p->business_id, 'Cobro anulado', 'job', $p->job_id, ['payment_id' => $p->id, 'reason' => $reason]);
            }
        });
    }

    private function fail(string $message): never
    {
        throw ValidationException::withMessages(['workflow' => $message]);
    }
}
