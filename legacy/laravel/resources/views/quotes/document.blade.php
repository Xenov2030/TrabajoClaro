<x-layout :title="'Presupuesto #' . $snapshot['number']" :minimal="$public" :printable="true">
    @php($s = $snapshot)
@php($expired = $version->expires_on < now($s['business']['timezone'])->toDateString())
    <article class="document">
        <div class="document-product-mark"><x-brand-logo variant="mono" /><span>Preparado con TrabajoClaro</span></div>
        <header class="document-head">
            <div><span class="eyebrow">{{ $s['business']['name'] }}</span>
                <h1>Presupuesto #{{ str_pad($s['number'], 3, '0', STR_PAD_LEFT) }}</h1>
                <p>Versión {{ $version->version }} ·
                    {{ $version->status }}{{ $expired ? ' · vencido' : '' }}{{ $version->revoked_at ? ' · enlace revocado' : '' }}
                </p>
            </div>
            <div>
                <p>Fecha: {{ $s['issued_on'] }}</p>
                <p>Válido hasta: {{ $s['expires_on'] }}</p>
            </div>
        </header>
        <div class="document-customer"><small class="muted">PREPARADO PARA</small>
            <h2>{{ $s['customer']['name'] }}</h2>
            <p>{{ $s['customer']['address'] }}</p>
        </div>
        <h2>{{ $s['title'] }}</h2>
        <h3>Alcance</h3>
        <p class="preserve">{{ $s['scope'] }}</p>
        @if ($s['exclusions'] ?? null)
            <h3>No incluye</h3>
            <p class="preserve">{{ $s['exclusions'] }}</p>
        @endif
        <div class="table-scroll quote-table">
            <table>
                <thead>
                    <tr>
                        <th>Descripción</th>
                        <th>Cantidad</th>
                        <th>Unitario</th>
                        <th>Importe</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($s['items'] as $i)
                        <tr>
                            <td>{{ $i['description'] }}<small>{{ $i['category'] }}</small></td>
                            <td>{{ $i['quantity'] }}</td>
                            <td>{{ \App\Services\Money::display(\App\Services\Money::minor($i['unit_price']), $s['currency']) }}
                            </td>
                            <td>{{ \App\Services\Money::display($i['total_minor'], $s['currency']) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="quote-items-mobile">@foreach($s['items'] as $i)<article class="document-item"><h3>{{ $i['description'] }}</h3>@if($i['category'])<span class="pill">{{ $i['category'] }}</span>@endif<dl><div><dt>Cantidad</dt><dd>{{ $i['quantity'] }}</dd></div><div><dt>Precio unitario</dt><dd>{{ \App\Services\Money::display(\App\Services\Money::minor($i['unit_price']),$s['currency']) }}</dd></div><div><dt>Importe</dt><dd><strong>{{ \App\Services\Money::display($i['total_minor'],$s['currency']) }}</strong></dd></div></dl></article>@endforeach</div><x-totals :s="$s" />
        @if ($s['conditions'] ?? null)
            <h3>Condiciones</h3>
            <p class="preserve">{{ $s['conditions'] }}</p>
        @endif
        <p class="fineprint">Esta propuesta usa {{ $s['currency'] }}. No se realiza conversión de moneda.</p>
        <div class="no-print"><button type="button" class="button secondary print-button">Imprimir / guardar
                PDF</button>
            @if ($public && !$expired && !$version->revoked_at && $version->status === 'pendiente')
                <section class="decision-box"><span class="eyebrow">TU RESPUESTA</span>
                    <h2>Confirmá qué querés hacer</h2>
                    <p>Tu decisión quedará asociada a esta versión exacta.</p>
                    <form method="post" action="{{ url()->current() }}">@csrf<x-field name="name" label="Tu nombre"
                            required maxlength="120" autocomplete="name" />
                        <div class="field"><label for="decision">Decisión</label><select id="decision"
                                name="decision">
                                <option value="aceptado" @selected(old('decision') === 'aceptado')>Aceptar presupuesto</option>
                                <option value="rechazado" @selected(old('decision') === 'rechazado')>Rechazar presupuesto</option>
                                <option value="modificacion" @selected(old('decision') === 'modificacion')>Solicitar una modificación
                                </option>
                            </select></div><x-field name="message" label="Comentario (opcional)" type="textarea"
                            maxlength="2000" /><label class="check"><input type="checkbox" name="confirmed"
                                value="1" required> Confirmo que revisé la versión {{ $version->version }} y
                            quiero registrar esta decisión.</label><button class="button">Registrar mi decisión</button>
                    </form>
                </section>
            @elseif($public)
                <div class="notice">Esta versión ya no admite nuevas decisiones: fue respondida o venció.</div>
            @endif
            <p class="fineprint">Esta confirmación no es una firma digital certificada. No verificamos la identidad de
                quien responde ni garantizamos validez jurídica.</p>
        </div>
    </article>
</x-layout>
