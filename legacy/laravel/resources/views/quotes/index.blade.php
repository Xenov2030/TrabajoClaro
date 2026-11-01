<x-layout title="Presupuestos">
    <div class="page-heading">
        <div><span class="eyebrow">DEJÁ CLARO LO ACORDADO</span>
            <h1>Presupuestos</h1>
        </div><a class="button" href="/app/quotes/new">+ Nuevo presupuesto</a>
    </div>
    <section class="card">
        @forelse($quotes as $q)
            <a class="list-row" href="/app/quotes/{{ $q->id }}"><span class="document-icon">→</span>
                <div class="grow"><strong>#{{ str_pad($q->number, 3, '0', STR_PAD_LEFT) }} ·
                        {{ $q->draft['title'] }}</strong><small>Vence el borrador {{ $q->draft['expires_on'] }}</small>
                </div>
                <div class="align-right">
                    <strong>{{ \App\Services\Money::display($q->draft['total_minor'], $q->draft['currency']) }}</strong><span
                        class="pill">{{ $q->state() }}</span></div>
        </a>@empty<div class="empty">
                <h2>Tu primer acuerdo empieza acá</h2>
                <p>Creá un cliente y prepará una propuesta. Publicarla genera una versión que podés compartir.</p><a
                    href="/app/quotes/new">Crear presupuesto →</a>
            </div>
        @endforelse{{ $quotes->links() }}
    </section><a class="export-link" href="/app/export/quotes">↓ Exportar presupuestos y versiones en CSV</a>
</x-layout>
