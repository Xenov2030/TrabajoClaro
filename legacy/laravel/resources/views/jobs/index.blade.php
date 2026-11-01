<x-layout title="Trabajos">
    <div class="page-heading">
        <div><span class="eyebrow">DEL ACUERDO A LA ENTREGA</span>
            <h1>Trabajos</h1>
        </div><a class="button" href="/app/jobs/new">+ Trabajo manual</a>
    </div>
    <div class="view-switch"><a href="/app/jobs" @class(['active' => !$board])>Lista</a><a href="/app/jobs?view=board"
            @class(['active' => $board])>Tablero</a></div>
    @if ($board)
        <div class="board">
            @foreach (['pendiente', 'en curso', 'en espera', 'terminado', 'cancelado'] as $status)
                <section class="board-column">
                    <h2>{{ ucfirst($status) }} <span class="pill">{{ $jobs->where('status', $status)->count() }}</span>
                    </h2>
                    @forelse($jobs->where('status',$status) as $j)
                        <a class="board-card" href="/app/jobs/{{ $j->id }}"><strong>{{ $j->title }}</strong>
                            <p>{{ $j->next_action ?: 'Sin próxima acción' }}</p><small>Saldo
                                {{ \App\Services\Money::display($j->balance(), $j->currency) }}</small><small>{{ $j->due_on ? 'Entrega ' . $j->due_on : 'Sin fecha de entrega' }}</small>
                    </a>@empty<p class="muted">Sin trabajos en este estado.</p>
                    @endforelse
                </section>
            @endforeach
        </div>
    @else<section class="card">
            @forelse($jobs as $j)
                <a class="list-row" href="/app/jobs/{{ $j->id }}">
                    <div class="grow">
                        <strong>{{ $j->title }}</strong><small>{{ $j->next_action ?: 'Agregá una próxima acción' }}{{ $j->due_on ? ' · entrega ' . $j->due_on : '' }}</small>
                    </div>
                    <div class="align-right"><span class="pill">{{ $j->status }}</span><small>Saldo
                            {{ \App\Services\Money::display($j->balance(), $j->currency) }}</small></div>
            </a>@empty<div class="empty">
                    <h2>Acá sigue lo que acordaste</h2>
                    <p>Convertí un presupuesto aceptado o cargá un trabajo manual para empezar.</p><a
                        href="/app/jobs/new">Crear trabajo manual →</a>
                </div>
            @endforelse
        </section>
    @endif
    <a class="export-link" href="/app/export/jobs">↓ Exportar trabajos en CSV</a>
</x-layout>
