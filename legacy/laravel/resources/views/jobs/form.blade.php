<x-layout :title="$job->exists ? 'Editar trabajo' : 'Trabajo manual'"><a class="back-link" href="/app/jobs">← Trabajos</a>
    <div class="page-heading">
        <div><span class="eyebrow">ALCANCE, FECHAS Y PRÓXIMO PASO</span>
            <h1>{{ $job->exists ? 'Editar trabajo' : 'Trabajo manual' }}</h1>
        </div>
    </div>
    @if ($customers->isEmpty() && !$job->exists)
        <div class="notice">Necesitás un cliente activo para crear el trabajo. <a href="/app/customers/new">Crear cliente →</a></div>
    @endif
    <form class="detail-grid" method="post" action="/app/jobs{{ $job->exists ? '/' . $job->id : '' }}">@csrf<section
            class="card">
            <h2>El acuerdo</h2>
            <div class="field"><label for="customer_id">Cliente</label><select name="customer_id" id="customer_id"
                    required>
                    @if ($job->exists && !$customers->contains('id', $job->customer_id))
                        <option value="{{ $job->customer_id }}">Cliente archivado #{{ $job->customer_id }}</option>
                        @endif @foreach ($customers as $c)
                            <option value="{{ $c->id }}" @selected(old('customer_id', $job->customer_id) == $c->id)>{{ $c->name }}
                            </option>
                        @endforeach
                </select>
                <small class="muted">En un trabajo existente, el cliente se conserva.</small>
            </div><x-field name="title" label="Título del trabajo" :value="$job->title" required
                maxlength="160" /><x-field name="scope" label="Alcance acordado" type="textarea" :value="$job->scope"
                required maxlength="5000" />
            <div class="form-grid"><x-field name="amount" label="Importe acordado" :value="$job->exists ? \App\Services\Money::input($job->amount_minor) : ''"
                    inputmode="decimal" required />
                <div class="field"><label for="currency">Moneda</label><select id="currency" name="currency">
                        @foreach (['ARS', 'USD'] as $c)
                            <option @selected(old('currency', $job->currency ?? $business->currency) === $c)>{{ $c }}</option>
                        @endforeach
                    </select>
                    <small class="muted">Se conserva en trabajos existentes.</small>
                </div>
            </div>
            @if ($job->exists)
                <div class="calculation-note"><strong>Si cambiás el importe</strong><x-field name="change_reason"
                        label="Motivo del cambio" type="textarea" maxlength="1000" /><label class="check"><input
                            type="checkbox" name="confirm_change" value="1"> Confirmo el cambio del importe
                        acordado.</label></div>
            @endif
        </section>
        <section class="card">
            <h2>Cómo sigue</h2>
            <div class="field"><label for="status">Estado</label><select name="status" id="status">
                    @foreach (['pendiente', 'en curso', 'en espera', 'terminado', 'cancelado'] as $s)
                        <option @selected(old('status', $job->status ?? 'pendiente') === $s)>{{ $s }}</option>
                    @endforeach
                </select></div>
            <div class="form-grid"><x-field name="starts_on" label="Fecha de inicio (opcional)" type="date"
                    :value="$job->starts_on" /><x-field name="due_on" label="Fecha de entrega (opcional)" type="date"
                    :value="$job->due_on" /></div><x-field name="next_action" label="Próxima acción (opcional)"
                type="textarea" :value="$job->next_action" maxlength="1000"
                placeholder="Ej.: confirmar medidas con el cliente" /><x-field name="action_on"
                label="Fecha de la próxima acción" type="date" :value="$job->action_on" />
            <p class="muted">Terminar el trabajo y completar el cobro son cosas distintas.</p><button
                class="button full">Guardar trabajo</button>
        </section>
    </form>
</x-layout>
