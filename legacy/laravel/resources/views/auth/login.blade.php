<x-layout title="Ingresar">
    <div class="auth-wrap narrow"><a class="back-link" href="/">← Volver</a><x-brand-logo :tagline="true" class="auth-brand" /><span class="eyebrow">VOLVÉ A TU
            NEGOCIO</span>
        <h1>Todo en su lugar.</h1>
        <form class="card" method="post" action="/login">@csrf<x-field name="email" label="Correo" type="email"
                required autocomplete="email" /><x-field name="password" label="Contraseña" type="password" required
                autocomplete="current-password" /><button class="button full">Ingresar</button></form>
        <p>¿Primera vez? <a href="/register">Creá tu cuenta</a></p>
        <p class="muted">La recuperación de contraseña no está disponible en esta beta.</p>
    </div>
</x-layout>
