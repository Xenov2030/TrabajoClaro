<x-layout :title="$customer->exists ? $customer->name : 'Nuevo cliente'"><a class="back-link" href="/app/customers">← Clientes</a>
    <div class="page-heading">
        <h1>{{ $customer->exists ? $customer->name : 'Nuevo cliente' }}</h1>
        @if ($customer->archived_at)
            <span class="pill">Archivado</span>
        @endif
    </div>
    <div class="detail-grid">
        <form class="card" method="post" action="/app/customers{{ $customer->exists ? '/' . $customer->id : '' }}">@csrf
            <h2>Datos del cliente</h2><x-field name="name" label="Nombre" :value="$customer->name" required
                maxlength="120" /><x-field name="phone" label="WhatsApp / teléfono" :value="$customer->phone" type="tel"
                hint="Incluí + y el código de país. Ejemplo: +5491123456789." /><x-field name="email"
                label="Correo (opcional)" type="email" :value="$customer->email" maxlength="190" /><x-field name="address"
                label="Dirección (opcional)" :value="$customer->address" maxlength="500" /><x-field name="notes"
                label="Notas internas (opcionales)" type="textarea" :value="$customer->notes" maxlength="2000" /><button
                class="button">Guardar cliente</button></form>
        @if ($customer->exists)
            <aside>
                <section class="card">
                    <h2>Su recorrido</h2>
                    @forelse($quotes as $q)
                        <a class="list-row" href="/app/quotes/{{ $q->id }}">
                            <div><strong>Presupuesto #{{ $q->number }}</strong><small>{{ $q->draft['title'] }} ·
                                    {{ $q->state() }}</small></div>→
                    </a>@empty<p class="muted">Sin presupuestos todavía.</p>
                        @endforelse @foreach ($jobs as $j)
                            <a class="list-row" href="/app/jobs/{{ $j->id }}">
                                <div><strong>{{ $j->title }}</strong><small>{{ $j->status }} · saldo
                                        {{ \App\Services\Money::display($j->balance(), $j->currency) }}</small></div>→
                            </a>
                        @endforeach
                        <h3>
                            Movimientos</h3>
                        @forelse($payments as $p)
                            <p>{{ $p->paid_on }} · {{ \App\Services\Money::display($p->amount_minor, $p->currency) }}
                            {{ $p->voided_at ? '(anulado)' : '' }}</p>@empty<p class="muted">Sin cobros
                                registrados.</p>
                        @endforelse
                </section>
                @unless ($customer->archived_at)
                    <form class="card" method="post" action="/app/customers/{{ $customer->id }}/archive">@csrf<h3>
                            Archivar cliente</h3>
                        <p>Dejará de aparecer para nuevos documentos. Se conserva todo su historial.</p><button
                            class="button secondary">Archivar</button>
                    </form>
                @endunless
            </aside>
        @endif
    </div>
</x-layout>
