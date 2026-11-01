<x-layout :title="'Presupuesto #' . $quote->number">@php($d = $quote->draft)
    <a class="back-link" href="/app/quotes">← Presupuestos</a>
    <div class="page-heading">
        <div><span class="eyebrow">PRESUPUESTO #{{ str_pad($quote->number, 3, '0', STR_PAD_LEFT) }}</span>
            <h1>{{ $d['title'] }}</h1>
            <p>{{ $customer->name }} <span class="pill">{{ $quote->state() }}</span></p>
        </div>
        <div class="actions">
            @if ($quote->status !== 'aceptado')
                <a class="button secondary" href="/app/quotes/{{ $quote->id }}/edit">Editar borrador</a>
            @endif
            <form method="post" action="/app/quotes/{{ $quote->id }}/duplicate">
                @csrf<button class="button secondary">Duplicar</button></form>
        </div>
    </div>
    @if (session('share_url'))
        <section class="card share-card">
            <h2>Enlace privado de esta versión</h2><x-field name="share_url" label="Copiá y guardá el enlace"
                :value="session('share_url')" readonly /><a href="{{ session('share_url') }}" target="_blank" rel="noreferrer"
                class="button secondary">Ver como cliente →</a>
            @if ($customer->phone)
                <div class="field"><label for="whatsapp-message">Mensaje editable para WhatsApp</label>
                    <textarea id="whatsapp-message">Hola {{ $customer->name }}, te comparto el presupuesto de {{ $d['title'] }} para que puedas revisarlo: {{ session('share_url') }}</textarea>
                </div><a id="whatsapp-link" class="button" data-phone="{{ ltrim($customer->phone, '+') }}"
                    href="https://wa.me/{{ ltrim($customer->phone, '+') }}?text={{ rawurlencode('Hola ' . $customer->name . ', te comparto el presupuesto: ' . session('share_url')) }}"
                target="_blank" rel="noreferrer">Abrir WhatsApp →</a>@else<p>Agregá un teléfono con código de país
                    al cliente para abrir WhatsApp.</p>
            @endif
            <p class="fineprint">
                Vos decidís si lo enviás. Abrir WhatsApp no demuestra envío ni recepción.</p>
        </section>
    @endif
    <div class="detail-grid">
        <section class="card"><span class="eyebrow">BORRADOR ACTUAL</span>
            <h2>Detalle de la propuesta</h2>
            <p class="preserve">{{ $d['scope'] }}</p>
            @if ($d['exclusions'] ?? null)
                <h3>No incluye</h3>
                <p class="preserve">{{ $d['exclusions'] }}</p>
                @endif @foreach ($d['items'] as $item)
                    <div class="list-row">
                        <div><strong>{{ $item['description'] }}</strong><small>{{ $item['quantity'] }} ×
                                {{ \App\Services\Money::display(\App\Services\Money::minor($item['unit_price']), $d['currency']) }}</small>
                        </div><strong>{{ \App\Services\Money::display($item['total_minor'], $d['currency']) }}</strong>
                    </div>
                @endforeach
                <x-totals :s="$d" />
                <p class="preserve">{{ $d['conditions'] ?? '' }}</p>
        </section>
        <aside>
            <section class="card">
                <h2>Próximo paso</h2>
                @if ($quote->status === 'aceptado')
                    <p>El cliente aceptó una versión. Creá el trabajo con ese alcance e importe.</p>
                    <form method="post" action="/app/quotes/{{ $quote->id }}/convert">@csrf<button
                        class="button full">Abrir / crear trabajo →</button></form>@else<p>Publicá una versión para
                        compartir. Reemplaza y revoca cualquier enlace anterior de este presupuesto.</p>
                    <form method="post" action="/app/quotes/{{ $quote->id }}/publish">@csrf<button
                            class="button full">Publicar versión</button></form>
                @endif
                <p class="fineprint">
                    Publicar no equivale a enviar. Cambiar el borrador no cambia una versión publicada.</p>
            </section>
            <section class="card">
                <h2>Versiones publicadas</h2>
                @forelse($versions as $v)
                    <div class="version">
                        <div class="card-heading"><strong>Versión {{ $v->version }}</strong><span
                                class="pill">{{ $v->status === 'pendiente' && $v->expires_on < now($business->timezone)->toDateString() ? 'vencido' : $v->status }}{{ $v->revoked_at ? ' · enlace revocado' : '' }}</span>
                        </div>
                        <p>{{ \App\Services\Money::display($v->snapshot['total_minor'], $v->snapshot['currency']) }} ·
                            vence {{ $v->expires_on }}</p><a href="/app/versions/{{ $v->id }}/print"
                            target="_blank" rel="noreferrer">Vista imprimible / guardar PDF
                            →</a>@php($decision = \App\Models\QuoteDecision::where('business_id', $business->id)->where('quote_version_id', $v->id)->first())@if ($decision)
                            <p><strong>{{ $decision->name }}</strong> · {{ $decision->decision }} ·
                                {{ $decision->created_at->timezone($business->timezone)->format('d/m/Y H:i') }}</p>
                            <p>{{ $decision->message }}</p>
                        @endif @unless ($v->revoked_at)
                        <form method="post" action="/app/versions/{{ $v->id }}/revoke">@csrf<button
                                class="text-button danger">Revocar enlace</button></form>
                    @endunless
                </div>
                @empty<p class="muted">Todavía no publicaste una versión.</p>
                @endforelse
            </section>
        </aside>
    </div>
</x-layout>
