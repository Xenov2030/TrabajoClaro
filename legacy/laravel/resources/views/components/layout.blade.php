@props(['title' => 'TrabajoClaro', 'minimal' => false, 'printable' => false])
<!doctype html>
<html lang="es-AR">

<head>
    <meta charset="utf-8">
    <x-brand-meta />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description"
        content="Cada trabajo en orden. Cada cobro, claro. Presupuestos, trabajos y registro de cobros para pequeños negocios. Beta.">
    <title>{{ $title }} · TrabajoClaro</title>@vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="{{ $printable ? 'printable-page' : '' }}"><a href="#contenido" class="skip">Saltar al contenido</a>
    <header class="topbar">
        <div class="shell header-inner"><a href="{{ auth()->check() ? '/app' : '/' }}" class="brand" aria-label="TrabajoClaro · Inicio"><x-brand-logo /><small>beta</small></a>
            <div class="header-actions">
                @unless($minimal)
                @auth <span
                        class="muted business-name">{{ $business->name ?? auth()->user()->name }}</span>
                <form method="post" action="/logout">@csrf<button class="text-button">Salir</button></form>@else<a
                        href="/login" class="text-button">Ingresar</a><a href="/register" class="button small">Crear cuenta de prueba</a>@endauth
                @endunless
            </div>
        </div>
    </header>
    @isset($business)<nav class="appnav" aria-label="Principal">
            <div class="shell nav-inner">
                @foreach (['' => 'home', 'customers' => 'customers', 'quotes' => 'quotes', 'jobs' => 'jobs', 'settings' => 'settings'] as $path => $key)
                    <a href="/app{{ $path ? '/' . $path : '' }}" @class([
                        'active' => $path
                            ? request()->is('app/' . $path . '*')
                            : request()->is('app'),
                    ])>{{ __('ui.' . $key) }}</a>
                @endforeach
            </div>
        </nav>
        @if ($business->is_demo)
            <div class="demo-bar">DEMO · Datos ficticios de ejemplo. Espacio privado por visitante · vence en 24 h.</div>
        @endif
@endisset
        @unless($minimal)
        <aside class="beta-banner shell" aria-label="Versión de prueba"><span><strong>Beta {{ config('beta.version') }}</strong> · Probá con datos ficticios. <a href="/beta">Cómo funciona la prueba</a></span><a class="feedback-link button secondary small" href="/feedback?screen={{ \App\Services\Screen::fromPath(request()->path()) }}">Enviar feedback</a></aside>
        @endunless
        <main id="contenido" class="{{ isset($business) ? 'shell app-main' : '' }}">
            @if (session('notice'))
                <div class="notice {{ isset($business) ? '' : 'shell' }}" role="status">{{ session('notice') }}</div>
            @endif
            @if ($errors->any())
                <div class="error-summary {{ isset($business) ? '' : 'shell' }}" role="alert"><strong>Revisá estos
                        datos</strong>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            {{ $slot }}
        </main>
        <footer @class(['site-footer shell', 'printable-footer' => $printable])><span>TrabajoClaro · Beta {{ config('beta.version') }} <a href="/feedback?screen={{ \App\Services\Screen::fromPath(request()->path()) }}">Enviar feedback</a></span><x-author-signature /></footer>
    </body>

    </html>
