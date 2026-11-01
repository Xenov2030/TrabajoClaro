<x-layout title="Clientes">
    <div class="page-heading">
        <div><span class="eyebrow">PERSONAS Y NEGOCIOS</span>
            <h1>Clientes</h1>
        </div><a class="button" href="/app/customers/new">+ Nuevo cliente</a>
    </div>
    <form class="search-bar" method="get"><label class="sr-only" for="q">Buscar clientes</label><input
            name="q" id="q" value="{{ request('q') }}" placeholder="Buscar por nombre, teléfono o correo"
            maxlength="100"><label class="check"><input type="checkbox" name="archived" value="1"
                @checked(request('archived'))> Incluir archivados</label><button class="button secondary">Buscar</button>
    </form>
    <section class="card">
        @forelse($customers as $c)
            <a class="list-row" href="/app/customers/{{ $c->id }}"><span
                    class="avatar">{{ mb_substr($c->name, 0, 1) }}</span>
                <div class="grow">
                    <strong>{{ $c->name }}</strong><small>{{ $c->phone ?: ($c->email ?: 'Sin datos de contacto') }}</small>
                </div><span class="pill">{{ $c->archived_at ? 'Archivado' : 'Activo' }}</span><span>→</span>
        </a>@empty<div class="empty">
                <h2>No encontramos clientes</h2>
                <p>Probá otra búsqueda o agregá tu primer cliente para preparar un presupuesto.</p><a
                    href="/app/customers/new">Crear cliente →</a>
            </div>
        @endforelse{{ $customers->links() }}
    </section><a class="export-link" href="/app/export/customers">↓ Exportar clientes en CSV</a>
</x-layout>
