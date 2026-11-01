<x-layout title="Inicio">
    <div class="page-heading">
        <div><span class="eyebrow">{{ $business->name }} / {{ $today }}</span>
            <h1>{{ __('ui.attention') }}</h1>
            <p class="muted">Lo que sigue, lo que vence y lo que falta cobrar.</p>
        </div><a class="button" href="/app/quotes/new">+ Nuevo presupuesto</a>
    </div>
    <div class="summary-strip">
        <div><span>Pendientes de respuesta</span><strong>{{ $pending->count() }}</strong></div>
        <div><span>Acciones vencidas</span><strong>{{ $overdue->count() }}</strong></div>
        <div><span>Entregas hasta 7 días</span><strong>{{ $due->count() }}</strong></div>
        <div><span>Terminados con saldo</span><strong>{{ $unpaid->count() }}</strong></div>
    </div>
    <div class="dashboard-grid">
        <section class="card">
            <div class="card-heading">
                <h2>Próximas acciones vencidas</h2><span class="pill amber">{{ $overdue->count() }}</span>
            </div>
            @forelse($overdue as $j)
                <a class="list-row" href="/app/jobs/{{ $j->id }}">
                    <div><strong>{{ $j->next_action }}</strong><small>{{ $j->title }} ·
                            {{ $j->action_on }}</small></div><span aria-hidden="true">→</span>
            </a>@empty<div class="empty"><span class="empty-icon"><x-icon name="check"/></span>
                    <h3>Sin acciones vencidas</h3>
                    <p>Agregá una próxima acción a cada trabajo para saber cómo seguir.</p><a href="/app/jobs">Ver
                        trabajos →</a>
                </div>
            @endforelse
        </section>
        <section class="card">
            <h2>Esperando una respuesta</h2>
            @forelse($pending as $v)
                <a class="list-row" href="/app/quotes/{{ $v->quote_id }}">
                    <div><strong>#{{ str_pad($v->snapshot['number'], 3, '0', STR_PAD_LEFT) }} ·
                            {{ $v->snapshot['title'] }}</strong><small>{{ $v->snapshot['customer']['name'] }} · vence
                            {{ $v->expires_on }}</small></div><span>→</span>
            </a>@empty<div class="empty">
                    <h3>No hay presupuestos pendientes</h3>
                    <p>Al publicar una versión, aparecerá acá hasta su respuesta o vencimiento.</p>
                </div>
            @endforelse
        </section>
        <section class="card">
            <h2>Próximas entregas</h2>
            @forelse($due as $j)
                <a class="list-row" href="/app/jobs/{{ $j->id }}">
                    <div><strong>{{ $j->title }}</strong><small>Entrega {{ $j->due_on }} ·
                            {{ $j->status }}</small></div><span>→</span>
            </a>@empty<p class="empty">No hay entregas previstas para los próximos 7 días.</p>
            @endforelse
        </section>
        <section class="card">
            <h2>Terminados, falta cobrar</h2>
            @forelse($unpaid as $j)
                <a class="list-row"
                href="/app/jobs/{{ $j->id }}"><strong>{{ $j->title }}</strong><span>{{ \App\Services\Money::display($j->balance(), $j->currency) }}</span></a>@empty
                <p class="empty">No hay trabajos terminados con saldo pendiente.</p>
            @endforelse
        </section>
    </div>
    <section class="card collection-card">
        <div><span class="eyebrow">COBROS REGISTRADOS ESTE MES</span>
            <h2>{{ $month }} al {{ $today }}</h2>
            <p class="muted">Movimientos vigentes, separados por moneda. No representan ganancia.</p>
        </div>
        <div class="amounts">
            @forelse($collected as $c)
            <strong>{{ \App\Services\Money::display($c->total, $c->currency) }}</strong>@empty<span>Sin cobros
                    registrados en el período.</span>
            @endforelse
        </div>
    </section>
</x-layout>
