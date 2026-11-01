<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Job;
use App\Models\PaymentEntry;
use App\Models\Quote;
use App\Models\QuoteDecision;
use App\Models\QuoteVersion;
use App\Services\Csv;
use App\Services\Money;
use Illuminate\Http\Request;

class ExportController extends Controller
{
    public function __invoke(Request $r, string $type)
    {
        $b = $r->attributes->get('business');
        $models = ['customers' => Customer::class, 'quotes' => Quote::class, 'jobs' => Job::class, 'payments' => PaymentEntry::class];
        abort_unless(isset($models[$type]), 404);
        $headers = match ($type) {
            'customers' => ['id', 'nombre', 'telefono', 'correo', 'direccion', 'notas', 'archivado'],
            'quotes' => ['id', 'numero', 'cliente_id', 'titulo_borrador', 'estado', 'moneda_borrador', 'total_borrador', 'borrador_json', 'versiones_publicadas_json'],
            'jobs' => ['id', 'cliente_id', 'presupuesto_id', 'titulo', 'alcance', 'estado', 'moneda', 'importe', 'saldo', 'inicio', 'entrega', 'proxima_accion', 'fecha_accion'],
            'payments' => ['id', 'cliente_id', 'trabajo_id', 'fecha', 'moneda', 'importe', 'medio', 'referencia', 'anulado', 'motivo']
        };

        return response()->streamDownload(function () use ($models, $type, $b, $headers) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, $headers, ',', '"', '');
            foreach ($models[$type]::where('business_id', $b->id)->orderBy('id')->cursor() as $m) {
                $row = match ($type) {
                    'customers' => [$m->id, $m->name, $m->phone, $m->email, $m->address, $m->notes, $m->archived_at],
                    'quotes' => [$m->id, $m->number, $m->customer_id, $m->draft['title'], $m->state(), $m->draft['currency'], Money::input($m->draft['total_minor']), json_encode($m->draft, JSON_UNESCAPED_UNICODE), json_encode(QuoteVersion::where('business_id', $b->id)->where('quote_id', $m->id)->get(['id', 'version', 'snapshot', 'status', 'revoked_at', 'expires_on'])->map(function ($v) use ($b) {
                        $a = $v->toArray();
                        $a['decision'] = QuoteDecision::where('business_id', $b->id)->where('quote_version_id', $v->id)->first(['decision', 'name', 'message', 'created_at'])?->toArray();

                        return $a;
                    })->toArray(), JSON_UNESCAPED_UNICODE)],
                    'jobs' => [$m->id, $m->customer_id, $m->quote_id, $m->title, $m->scope, $m->status, $m->currency, Money::input($m->amount_minor), Money::input($m->balance()), $m->starts_on, $m->due_on, $m->next_action, $m->action_on],
                    'payments' => [$m->id, $m->customer_id, $m->job_id, $m->paid_on, $m->currency, Money::input($m->amount_minor), $m->method, $m->reference, $m->voided_at, $m->void_reason]
                };
                fputcsv($out, array_map([Csv::class, 'safe'], $row), ',', '"', '');
            }fclose($out);
        }, 'trabajoclaro-'.$type.'.csv', ['Content-Type' => 'text/csv; charset=UTF-8', 'Cache-Control' => 'no-store']);
    }
}
