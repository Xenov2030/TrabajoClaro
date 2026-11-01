<x-layout title="Configuración">
    <div class="page-heading">
        <div><span class="eyebrow">TU ESPACIO DE TRABAJO</span>
            <h1>Configuración</h1>
        </div>
    </div>
    <div class="detail-grid">
        <form class="card" method="post" action="/app/settings">@csrf<h2>Datos del negocio</h2><x-business-fields
                :b="$business" /><button class="button">Guardar configuración</button>
            <p class="muted">Los cambios se usan para documentos nuevos. No modifican versiones publicadas ni trabajos
                existentes.</p>
        </form>
        <aside>
            <section class="card">
                <h2>Tus datos, disponibles</h2>
                <p>Descargá la información de tu negocio. Los cobros anulados y las versiones publicadas también se
                    conservan.</p>
                @foreach (['customers' => 'Clientes', 'quotes' => 'Presupuestos y versiones', 'jobs' => 'Trabajos', 'payments' => 'Movimientos de cobro'] as $type => $label)
                    <a class="list-row"
                        href="/app/export/{{ $type }}"><strong>{{ $label }}</strong><span>CSV
                            ↓</span></a>
                @endforeach
            </section>
            <section class="card">
                <h2>Sobre esta beta</h2>
                <p>No procesa pagos ni reemplaza facturación fiscal o contabilidad.</p>
                <p>Las confirmaciones de presupuestos no son firmas digitales certificadas.</p>
                <p>No se envían datos a servicios externos de analítica. Las métricas internas excluyen las demos.</p>
                <p class="muted">Las políticas legales y de privacidad deben revisarse antes del lanzamiento público.
                </p>
            </section>
        </aside>
    </div>
</x-layout>
