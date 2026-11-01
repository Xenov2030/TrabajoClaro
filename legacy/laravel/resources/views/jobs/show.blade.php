<x-layout :title="$job->title"><a class="back-link" href="/app/jobs">← Trabajos</a>
    <div class="page-heading">
        <div><span class="eyebrow">TRABAJO #{{ $job->id }} · {{ $customer->name }}</span>
            <h1>{{ $job->title }}</h1><span class="pill">{{ $job->status }}</span>
        </div><a class="button secondary" href="/app/jobs/{{ $job->id }}/edit">Actualizar trabajo</a>
    </div>
    <div class="summary-strip three">
        <div><span>Importe acordado</span><strong
                class="money">{{ \App\Services\Money::display($job->amount_minor, $job->currency) }}</strong></div>
        <div><span>Cobros vigentes</span><strong
                class="money">{{ \App\Services\Money::display($job->amount_minor - $job->balance(), $job->currency) }}</strong>
        </div>
        <div><span>Saldo pendiente</span><strong
                class="money accent">{{ \App\Services\Money::display($job->balance(), $job->currency) }}</strong></div>
    </div>
    <div class="detail-grid">
        <div>
            <section class="card">
                <h2>Qué hay que entregar</h2>
                <p class="preserve">{{ $job->scope }}</p>
                <div class="form-grid">
                    <p><small class="muted">INICIO</small><br>{{ $job->starts_on ?: 'Sin fecha' }}</p>
                    <p><small class="muted">ENTREGA</small><br>{{ $job->due_on ?: 'Sin fecha' }}</p>
                </div>
                <div class="next-action"><span class="eyebrow">PRÓXIMA ACCIÓN</span>
                    <h3>{{ $job->next_action ?: 'Todavía no definiste el próximo paso' }}</h3>
                    <p>{{ $job->action_on ?: 'Sin fecha' }}</p>
                </div>
            </section>
            <section class="card">
                <h2>Movimientos registrados</h2>
                <p class="fineprint">{{ __('ui.internal') }}</p>
                @forelse($payments as $p)
                    <div class="payment-row">
                        <div class="card-heading">
                            <strong>{{ \App\Services\Money::display($p->amount_minor, $p->currency) }}</strong><span
                                class="pill">{{ $p->voided_at ? 'Anulado' : 'Vigente' }}</span></div>
                        <p>{{ $p->paid_on }} · {{ $p->method }} · {{ $p->reference }}</p>
                        @if ($p->voided_at)
                        <p class="muted">Motivo: {{ $p->void_reason }}</p>@else<details>
                                <summary>Anular con motivo</summary>
                                <form method="post" action="/app/payments/{{ $p->id }}/void">@csrf<label
                                        for="reason-{{ $p->id }}">Motivo de anulación</label><input
                                        id="reason-{{ $p->id }}" name="reason" required minlength="3"
                                        maxlength="1000"><button class="button secondary">Confirmar anulación</button>
                                </form>
                            </details>
                        @endif
                    </div>
                @empty<p class="empty">No hay cobros registrados para este trabajo.</p>
                @endforelse
            </section>
            <section class="card">
                <h2>Historial del trabajo</h2>
                @foreach ($events as $e)
                    <div class="history-event">
                        <small>{{ $e->created_at->timezone($business->timezone)->format('d/m/Y H:i') }}</small><strong>{{ $e->name }}</strong>
                        @if ($e->details)
                            @foreach ($e->details as $key => $value)
                                <p class="muted">
                                    {{ ['previous_minor' => 'Importe anterior en centavos', 'new_minor' => 'Nuevo importe en centavos', 'reason' => 'Motivo', 'payment_id' => 'Movimiento', 'previous_status' => 'Estado anterior', 'new_status' => 'Nuevo estado'][$key] ?? $key }}:
                                    {{ $value }}</p>
                            @endforeach
                        @endif
                    </div>
                @endforeach
            </section>
        </div>
        <aside>
            <section class="card sticky"><span class="eyebrow">SIN PROCESAR PAGOS</span>
                <h2>Registrar un cobro</h2>
                @if ($job->balance() > 0)
                    <p>Cargá un anticipo o pago que ya recibiste.</p>
                    <form method="post" action="/app/jobs/{{ $job->id }}/payments">@csrf<input type="hidden"
                            name="idempotency_key"
                            value="{{ old('idempotency_key', (string) Illuminate\Support\Str::uuid()) }}"><input
                            type="hidden" name="currency" value="{{ $job->currency }}"><x-field name="amount"
                            :label="'Importe en ' . $job->currency" required inputmode="decimal"
                            hint="Usá punto decimal, sin separadores de miles." /><x-field name="paid_on"
                            label="Fecha del cobro" type="date" :value="now($business->timezone)->toDateString()" :max="now($business->timezone)->toDateString()" required />
                        <div class="field"><label for="method">Medio</label><select name="method" id="method">
                                @foreach (['transferencia', 'efectivo', 'tarjeta', 'otro'] as $m)
                                    <option @selected(old('method') === $m)>{{ $m }}</option>
                                @endforeach
                            </select>
                        </div><x-field name="reference" label="Referencia (opcional)" maxlength="190" /><button
                            class="button full">Registrar cobro</button>
                    </form>
                    <p class="fineprint">No se admiten pagos superiores al saldo. Para corregir un movimiento, anulalo
                    con motivo y registralo nuevamente.</p>@else<div class="empty"><span
                            class="empty-icon"><x-icon name="check"/></span>
                        <h3>Saldo completo</h3>
                        <p>Todos los cobros del importe acordado están registrados.</p>
                    </div>
                @endif
                <p class="fineprint">{{ __('ui.internal') }}. No guardes datos de tarjetas.</p>
            </section>
        </aside>
    </div>
</x-layout>
