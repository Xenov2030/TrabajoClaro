<x-layout title="Cada trabajo en orden. Cada cobro, claro.">
    <section class="hero shell">
        <div class="hero-copy"><span class="eyebrow"><span class="status-dot"></span> Para negocios que hacen cosas a
                medida</span>
            <h1>Cada trabajo<br> en orden.<br> <em>Cada cobro, claro.</em></h1>
            <p class="hero-lead">Tené claro qué aprobaron, qué debés entregar y cuánto falta cobrar.</p>
            <p class="muted">Presupuestos, trabajos y cobros en un solo lugar. Para que seguir cada encargo no dependa
                de encontrar un mensaje.</p>
            <div class="actions">@guest<form action="/demo" method="post">@csrf<button class="button">Probar demo <span
                                aria-hidden="true">→</span></button></form><a class="button secondary"
                    href="/register">Crear cuenta de prueba</a>@else<a class="button" href="/app">Ir a mi negocio
                    →</a>@endguest
            </div><small class="muted">Beta · Demo con ejemplos · Sin tarjeta</small>
        </div>
        <div class="hero-demo" aria-label="Ejemplo del recorrido de un trabajo">
            <div class="demo-heading"><span class="eyebrow">UN ENCARGO, TODO EL RECORRIDO</span><span
                    class="example-tag">Ejemplo ficticio</span></div>
            <div class="example-customer"><span class="avatar">ML</span>
                <div><small class="muted">Cliente</small>
                    <h3>Marina López</h3>
                </div><span class="muted">→</span>
            </div>
            <div class="example-title"><span class="muted">PRESUPUESTO #001</span>
                <h2>Mueble bajo mesada</h2>
                <p>Fabricación e instalación · ARS</p>
            </div>
            <ol class="journey">
                <li><span class="journey-dot"><x-icon name="check"/></span>
                    <div><strong>Presupuesto aceptado</strong><small>Alcance y condiciones confirmados</small></div>
                    <span class="pill">Listo</span>
                </li>
                <li><span class="journey-dot current">2</span>
                    <div><strong>Trabajo en curso</strong><small>Próximo paso: confirmar medidas</small></div>
                </li>
                <li><span class="journey-dot neutral">3</span>
                    <div><strong>Saldo por cobrar</strong><small>Anticipo registrado · saldo separado</small></div>
                </li>
            </ol>
            <div class="example-footer"><span class="status-dot"></span> Cada paso con su información</div>
        </div>
    </section>
    <section class="problem-band">
        <div class="shell"><span class="eyebrow">MENOS BÚSQUEDA. MÁS CLARIDAD.</span>
            <h2>¿Qué habíamos acordado?</h2>
            <p>Si esa pregunta te lleva a revisar chats, papeles y planillas, probá reunir lo importante de cada
                trabajo. Si tus herramientas actuales te alcanzan, también está bien.</p>
        </div>
    </section>
    <section class="shell section">
        <div class="section-intro"><span class="eyebrow">DEL PEDIDO AL CIERRE</span>
            <h2>Un recorrido simple.<br>Sin cargar lo mismo dos veces.</h2>
        </div>
        <div class="feature-grid">
            <article><span class="step-number">01 / ACORDAR</span>
                <h3>Un presupuesto claro</h3>
                <p>Detallá alcance, ítems y condiciones. Compartí una versión que tu cliente puede responder sin crear
                    cuenta.</p>
            </article>
            <article><span class="step-number">02 / HACER</span>
                <h3>El próximo paso, a mano</h3>
                <p>Convertí lo aceptado en trabajo. Seguí fechas, estados y próximas acciones desde el celular.</p>
            </article>
            <article><span class="step-number">03 / COBRAR</span>
                <h3>Sabé cuánto falta</h3>
                <p>Registrá anticipos y pagos parciales. Consultá saldos por moneda y exportá tus datos cuando
                    necesites.</p>
            </article>
        </div>
        <p class="fineprint">Registro interno: no procesa pagos, no reemplaza facturación fiscal ni contabilidad y no
            consigue clientes automáticamente.</p>
    </section>
    <section class="shell pilot-section" id="piloto">
        <div><span class="eyebrow">CONSTRUIDO PARA PROBAR EN NEGOCIOS REALES</span>
            <h2>¿Lo probamos<br>con tu próximo trabajo?</h2>
            <p>Buscamos conversar con instaladores, técnicos, carpinterías, herrerías y cartelerías. Contanos cómo te
                organizás hoy.</p>
            <p class="muted">Producto en beta. Sin testimonios inventados ni promesas de resultados.</p>
        </div>
        <form class="card" method="post" action="/pilot">@csrf<h3>Solicitar un piloto</h3><x-field name="name"
                label="Tu nombre" required maxlength="120" autocomplete="name" /><x-field name="email" label="Correo"
                type="email" required maxlength="190" autocomplete="email" /><x-field name="message"
                label="¿Qué tipo de trabajos hacés y cómo los seguís?" type="textarea" required minlength="15"
                maxlength="2000" />
            <div class="honeypot" aria-hidden="true"><label for="website">Dejá vacío este campo</label><input
                    name="website" id="website" tabindex="-1" autocomplete="off"></div><button class="button">Guardar
                solicitud →</button><small class="muted">Guardamos tu solicitud para evaluar el piloto. No se envían
                correos automáticos. Usá solo datos necesarios para contactarte.</small>
        </form>
    </section>
</x-layout>
