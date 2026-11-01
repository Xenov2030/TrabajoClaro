<?php

namespace App\Http\Controllers;

use App\Models\ActivityEvent;
use App\Models\Business;
use App\Models\Customer;
use App\Models\Job;
use App\Models\PaymentEntry;
use App\Models\Quote;
use App\Models\QuoteVersion;
use App\Services\Money;
use App\Services\Workflow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AppController extends Controller
{
    private function b(Request $r): Business
    {
        return $r->attributes->get('business');
    }

    private function owned(Request $r, string $model, int $id)
    {
        return $model::where('business_id', $this->b($r)->id)->findOrFail($id);
    }

    private function customers(Request $r)
    {
        return Customer::where('business_id', $this->b($r)->id)->whereNull('archived_at')->orderBy('name')->get();
    }

    public function dashboard(Request $r)
    {
        $b = $this->b($r);
        $today = now($b->timezone)->toDateString();
        $month = now($b->timezone)->startOfMonth()->toDateString();
        $pending = QuoteVersion::where('business_id', $b->id)->where('status', 'pendiente')->whereNull('revoked_at')->where('expires_on', '>=', $today)->get();
        $jobs = Job::where('business_id', $b->id)->orderBy('due_on')->get();
        $overdue = $jobs->filter(fn ($j) => ! in_array($j->status, ['terminado', 'cancelado']) && $j->next_action && $j->action_on && $j->action_on < $today);
        $due = $jobs->filter(fn ($j) => ! in_array($j->status, ['terminado', 'cancelado']) && $j->due_on && $j->due_on <= now($b->timezone)->addDays(7)->toDateString());
        $unpaid = $jobs->filter(fn ($j) => $j->status === 'terminado' && $j->balance() > 0);
        $collected = PaymentEntry::where('business_id', $b->id)->whereNull('voided_at')->whereBetween('paid_on', [$month, $today])->select('currency')->selectRaw('SUM(amount_minor) as total')->groupBy('currency')->get();

        return view('dashboard', compact('pending', 'overdue', 'due', 'unpaid', 'collected', 'today', 'month'));
    }

    public function customerIndex(Request $r)
    {
        $query = Customer::where('business_id', $this->b($r)->id);
        if (! $r->boolean('archived')) {
            $query->whereNull('archived_at');
        }
        $search = mb_substr((string) $r->query('q', ''), 0, 100);
        if ($search !== '') {
            $query->where(fn ($q) => $q->where('name', 'like', '%'.$search.'%')->orWhere('phone', 'like', '%'.$search.'%')->orWhere('email', 'like', '%'.$search.'%'));
        }

        return view('customers.index', ['customers' => $query->orderBy('name')->paginate(20)->withQueryString()]);
    }

    public function customerForm(Request $r, ?int $id = null)
    {
        $customer = $id ? $this->owned($r, Customer::class, $id) : new Customer;
        $quotes = $id ? Quote::where('business_id', $this->b($r)->id)->where('customer_id', $id)->latest()->get() : collect();
        $jobs = $id ? Job::where('business_id', $this->b($r)->id)->where('customer_id', $id)->latest()->get() : collect();
        $payments = $id ? PaymentEntry::where('business_id', $this->b($r)->id)->where('customer_id', $id)->latest()->get() : collect();

        return view('customers.form', compact('customer', 'quotes', 'jobs', 'payments'));
    }

    public function customerSave(Request $r, ?int $id = null)
    {
        $c = $id ? $this->owned($r, Customer::class, $id) : new Customer(['business_id' => $this->b($r)->id]);
        $r->merge(['phone' => preg_replace('/[\s().-]+/', '', (string) $r->input('phone', '')) ?: null]);
        $data = $r->validate(['name' => 'required|string|max:120', 'phone' => ['nullable', 'regex:/^\+[1-9][0-9]{7,14}$/'], 'email' => 'nullable|email|max:190', 'address' => 'nullable|string|max:500', 'notes' => 'nullable|string|max:2000']);
        $c->fill($data)->save();

        return redirect('/app/customers/'.$c->id)->with('notice', 'Cliente guardado.');
    }

    public function customerArchive(Request $r, int $id)
    {
        $c = $this->owned($r, Customer::class, $id);
        $c->update(['archived_at' => now()]);

        return redirect('/app/customers')->with('notice', 'Cliente archivado. Se conserva su historial.');
    }

    public function quoteIndex(Request $r)
    {
        return view('quotes.index', ['quotes' => Quote::where('business_id', $this->b($r)->id)->latest()->paginate(20)]);
    }

    public function quoteForm(Request $r, ?int $id = null)
    {
        $quote = $id ? $this->owned($r, Quote::class, $id) : new Quote;

        return view('quotes.form', ['quote' => $quote, 'customers' => $this->customers($r)]);
    }

    private function quoteData(Request $r): array
    {
        $data = $r->validate(['customer_id' => 'required|integer', 'title' => 'required|string|max:160', 'scope' => 'required|string|max:5000', 'exclusions' => 'nullable|string|max:3000', 'issued_on' => 'required|date_format:Y-m-d', 'expires_on' => 'required|date_format:Y-m-d|after_or_equal:issued_on', 'currency' => 'required|in:ARS,USD', 'discount' => 'nullable|string|max:12', 'tax_rate' => 'nullable|string|max:6', 'advance' => 'nullable|string|max:12', 'conditions' => 'nullable|string|max:3000', 'items' => 'required|array|min:1|max:30', 'items.*.description' => 'required|string|max:500', 'items.*.quantity' => 'required|string|max:14', 'items.*.unit_price' => 'required|string|max:12', 'items.*.category' => 'nullable|in:material,mano de obra']);
        foreach (['discount', 'tax_rate', 'advance'] as $key) {
            $data[$key] = $data[$key] ?? '0';
        }

        return $data;
    }

    public function quoteSave(Request $r, Workflow $flow, ?int $id = null)
    {
        $q = $id ? $this->owned($r, Quote::class, $id) : null;
        $data = $this->quoteData($r);
        $this->owned($r, Customer::class, (int) $data['customer_id']);
        if (! $q) {
            $q = $flow->createQuote($this->b($r)->id, (int) $data['customer_id'], $data);
        } else {
            DB::transaction(function () use ($q, $data, $flow) {
                $locked = Quote::lockForUpdate()->findOrFail($q->id);
                if ($locked->status === 'aceptado') {
                    throw ValidationException::withMessages(['workflow' => 'Duplicá el presupuesto aceptado para crear otra propuesta.']);
                }
                if (Customer::findOrFail($data['customer_id'])->archived_at) {
                    throw ValidationException::withMessages(['customer_id' => 'Elegí un cliente activo.']);
                }
                $locked->update(['customer_id' => $data['customer_id'], 'draft' => Money::quote($data)]);
                $flow->activity($q->business_id, 'Borrador actualizado', 'quote', $q->id);
            });
        }

        return redirect('/app/quotes/'.$q->id)->with('notice', 'Borrador guardado. Las versiones publicadas conservan sus datos.');
    }

    public function quoteShow(Request $r, int $id)
    {
        $quote = $this->owned($r, Quote::class, $id);
        $versions = QuoteVersion::where('business_id', $quote->business_id)->where('quote_id', $quote->id)->latest()->get();
        $customer = $this->owned($r, Customer::class, $quote->customer_id);

        return view('quotes.show', compact('quote', 'versions', 'customer'));
    }

    public function publish(Request $r, Workflow $flow, int $id)
    {
        $q = $this->owned($r, Quote::class, $id);
        [$v,$token] = $flow->publish($q);

        return back()->with('share_url', url('/p/'.$token))->with('notice', 'Versión publicada. Guardá el enlace ahora: por seguridad no podemos recuperarlo. Publicar no significa enviar.');
    }

    public function revoke(Request $r, Workflow $flow, int $id)
    {
        $v = $this->owned($r, QuoteVersion::class, $id);
        DB::transaction(function () use ($v, $flow) {
            $q = Quote::lockForUpdate()->findOrFail($v->quote_id);
            $v = QuoteVersion::lockForUpdate()->findOrFail($v->id);
            $v->update(['revoked_at' => now()]);
            $flow->activity($v->business_id, 'Enlace revocado', 'quote', $v->quote_id, ['version' => $v->version]);
            if ($q->status === 'pendiente') {
                $q->update(['status' => 'borrador']);
            }
        });

        return back()->with('notice', 'Enlace revocado. Las decisiones previas se conservan.');
    }

    public function duplicate(Request $r, Workflow $flow, int $id)
    {
        $q = $this->owned($r, Quote::class, $id);
        $d = $q->draft;
        $d['issued_on'] = now($this->b($r)->timezone)->toDateString();
        $d['expires_on'] = now($this->b($r)->timezone)->addDays(15)->toDateString();
        $new = $flow->createQuote($q->business_id, $q->customer_id, $d);

        return redirect('/app/quotes/'.$new->id.'/edit');
    }

    public function convert(Request $r, Workflow $flow, int $id)
    {
        $j = $flow->convert($this->owned($r, Quote::class, $id));

        return redirect('/app/jobs/'.$j->id);
    }

    public function versionPrint(Request $r, int $id)
    {
        $v = $this->owned($r, QuoteVersion::class, $id);

        return view('quotes.document', ['snapshot' => $v->snapshot, 'version' => $v, 'public' => false]);
    }

    public function jobIndex(Request $r)
    {
        return view('jobs.index', ['jobs' => Job::where('business_id', $this->b($r)->id)->latest()->get(), 'board' => $r->query('view') === 'board']);
    }

    public function jobForm(Request $r, ?int $id = null)
    {
        return view('jobs.form', ['job' => $id ? $this->owned($r, Job::class, $id) : new Job, 'customers' => $this->customers($r)]);
    }

    public function jobSave(Request $r, Workflow $flow, ?int $id = null)
    {
        $j = $id ? $this->owned($r, Job::class, $id) : null;
        $data = $r->validate(['customer_id' => 'required|integer', 'title' => 'required|string|max:160', 'scope' => 'required|string|max:5000', 'amount' => 'required|string|max:12', 'currency' => 'required|in:ARS,USD', 'status' => 'required|in:pendiente,en curso,en espera,terminado,cancelado', 'starts_on' => 'nullable|date_format:Y-m-d', 'due_on' => ['nullable', 'date_format:Y-m-d', ...($r->filled('starts_on') ? ['after_or_equal:starts_on'] : [])], 'next_action' => 'nullable|string|max:1000', 'action_on' => 'nullable|date_format:Y-m-d', 'change_reason' => 'nullable|string|max:1000', 'confirm_change' => 'sometimes|accepted']);
        $customer = $this->owned($r, Customer::class, (int) $data['customer_id']);
        $minor = Money::minor($data['amount']);
        if ($customer->archived_at && (! $j || $j->customer_id !== $customer->id)) {
            throw ValidationException::withMessages(['customer_id' => 'Elegí un cliente activo.']);
        }
        DB::transaction(function () use (&$j, $data, $minor, $flow, $r) {
            $details = [];
            if ($j) {
                $j = Job::lockForUpdate()->findOrFail($j->id);
                if ($data['currency'] !== $j->currency || (int) $data['customer_id'] !== $j->customer_id) {
                    throw ValidationException::withMessages(['currency' => 'El cliente y la moneda se conservan en un trabajo existente.']);
                }
                if ($minor !== (int) $j->amount_minor) {
                    if (empty($data['confirm_change']) || empty($data['change_reason'])) {
                        throw ValidationException::withMessages(['change_reason' => 'Confirmá el cambio de importe y documentá el motivo.']);
                    }
                    if ($minor < (int) $j->payments()->whereNull('voided_at')->sum('amount_minor')) {
                        throw ValidationException::withMessages(['amount' => 'El importe no puede ser menor a lo ya cobrado.']);
                    }
                    $details = ['previous_minor' => $j->amount_minor, 'new_minor' => $minor, 'reason' => $data['change_reason']];
                }
                $details['changes'] = json_encode(['before' => $j->only(['title', 'scope', 'starts_on', 'due_on', 'next_action', 'action_on']), 'after' => collect($data)->only(['title', 'scope', 'starts_on', 'due_on', 'next_action', 'action_on'])->all()], JSON_UNESCAPED_UNICODE);
                $details['previous_status'] = $j->status;
                $details['new_status'] = $data['status'];
            }
            $fields = collect($data)->except(['amount', 'change_reason', 'confirm_change'])->all();
            $fields['amount_minor'] = $minor;
            if ($j) {
                $j->update($fields);
                $flow->activity($j->business_id, 'Trabajo actualizado', 'job', $j->id, $details);
            } else {
                $j = Job::create($fields + ['business_id' => $this->b($r)->id]);
                $flow->activity($j->business_id, 'Trabajo manual creado', 'job', $j->id);
                $flow->metric($j->business_id, 'job_created');
            }
        });

        return redirect('/app/jobs/'.$j->id)->with('notice', 'Trabajo guardado.');
    }

    public function jobShow(Request $r, int $id)
    {
        $job = $this->owned($r, Job::class, $id);

        return view('jobs.show', ['job' => $job, 'customer' => $this->owned($r, Customer::class, $job->customer_id), 'payments' => $job->payments()->latest()->get(), 'events' => ActivityEvent::where('business_id', $job->business_id)->where('subject_type', 'job')->where('subject_id', $job->id)->latest()->get()]);
    }

    public function pay(Request $r, Workflow $flow, int $id)
    {
        $j = $this->owned($r, Job::class, $id);
        $d = $r->validate(['amount' => 'required|string|max:12', 'currency' => 'required|in:ARS,USD', 'paid_on' => 'required|date_format:Y-m-d|before_or_equal:'.now($this->b($r)->timezone)->toDateString(), 'method' => 'required|in:transferencia,efectivo,tarjeta,otro', 'reference' => 'nullable|string|max:190', 'idempotency_key' => 'required|uuid']);
        $flow->pay($j, $d);

        return back()->with('notice', 'Cobro registrado.');
    }

    public function void(Request $r, Workflow $flow, int $id)
    {
        $p = $this->owned($r, PaymentEntry::class, $id);
        $d = $r->validate(['reason' => 'required|string|min:3|max:1000']);
        $flow->void($p, $d['reason']);

        return back()->with('notice', 'Movimiento anulado con historial.');
    }

    public function settings(Request $r)
    {
        return view('settings');
    }

    public function settingsSave(Request $r)
    {
        $d = $r->validate(['name' => 'required|string|max:120', 'industry' => 'required|string|max:100', 'country' => 'required|in:AR,UY,CL,BO,BR,PY,PE,CO,MX,ES,US', 'timezone' => 'required|timezone', 'currency' => 'required|in:ARS,USD']);
        $this->b($r)->update($d);

        return back()->with('notice', 'Configuración guardada. No cambia documentos existentes.');
    }
}
