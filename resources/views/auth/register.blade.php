<x-layout title="Crear cuenta">
    <div class="auth-wrap"><a class="back-link" href="/">← Volver</a><x-brand-logo :tagline="true" class="auth-brand" /><span class="eyebrow">TU NEGOCIO, EN ORDEN</span>
        <h1>Empezá con un trabajo.</h1>
        <p class="muted">Creá tu espacio. Tus clientes responden presupuestos sin registrarse.</p>
        <form class="card" method="post" action="/register">@csrf<h2>Tu cuenta</h2><x-field name="name"
                label="Tu nombre" required maxlength="120" autocomplete="name" /><x-field name="email" label="Correo"
                type="email" required maxlength="190" autocomplete="email" /><x-field name="password"
                label="Contraseña" type="password" required minlength="10" autocomplete="new-password"
                hint="Al menos 10 caracteres." /><x-field name="password_confirmation" label="Repetí la contraseña"
                type="password" required autocomplete="new-password" />
            <hr>
            <h2>Tu negocio</h2><x-business-fields /><button class="button full">Crear cuenta de prueba</button>
            <p class="muted">Beta para pruebas. No cargues datos sensibles innecesarios. No contamos aún con políticas
                legales revisadas para un lanzamiento público.</p>
        </form>
        <p>¿Ya tenés cuenta? <a href="/login">Ingresá</a></p>
    </div>
</x-layout>
