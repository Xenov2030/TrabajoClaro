<x-layout :title="$quote->exists ? 'Editar borrador' : 'Nuevo presupuesto'">
    @php($d = $quote->draft ?? [])
    <a class="back-link" href="/app/quotes">← Presupuestos</a>
    <div class="page-heading">
        <div><span class="eyebrow">PASO 1 / PREPARAR LA PROPUESTA</span>
            <h1>{{ $quote->exists ? 'Editar borrador #' . $quote->number : 'Nuevo presupuesto' }}</h1>
            <p class="muted">Guardá primero. Revisá los totales antes de publicar.</p>
        </div>
    </div>
    @if ($customers->isEmpty())
        <div class="notice">Primero necesitás un cliente activo. <a href="/app/customers/new">Crear cliente →</a></div>
    @else
        <form action="/app/quotes{{ $quote->exists ? '/' . $quote->id : '' }}" method="post">@csrf
            <div class="detail-grid">
                <div>
                    <section class="card">
                        <h2>La propuesta</h2>
                        <div class="field"><label for="customer_id">Cliente *</label><select name="customer_id"
                                id="customer_id" required>
                                @foreach ($customers as $c)
                                    <option value="{{ $c->id }}" @selected(old('customer_id', $quote->customer_id) == $c->id)>{{ $c->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('customer_id')
                                <small class="error">{{ $message }}</small>
                            @enderror
                        </div>
                        <x-field name="title" label="Título" :value="$d['title'] ?? ''" required maxlength="160"
                            placeholder="Ej.: Mueble bajo mesada" /><x-field name="scope"
                            label="Qué incluye el trabajo" type="textarea" :value="$d['scope'] ?? ''" required
                            maxlength="5000" /><x-field name="exclusions" label="Qué no incluye (opcional)"
                            type="textarea" :value="$d['exclusions'] ?? ''" maxlength="3000" />
                        <div class="form-grid"><x-field name="issued_on" label="Fecha" type="date" :value="$d['issued_on'] ?? now($business->timezone)->toDateString()"
                                required /><x-field name="expires_on" label="Válido hasta" type="date"
                                :value="$d['expires_on'] ?? now($business->timezone)->addDays(15)->toDateString()" required /></div>
                    </section>
                    <section class="card">
                        <h2>Ítems</h2>
                        <p class="muted">Cantidades: hasta 3 decimales. Precios: hasta 2. Usá punto decimal, sin
                            separadores de miles.</p>
                        <div id="quote-items">
                            @foreach (old('items', $d['items'] ?? [['description' => '', 'quantity' => '1', 'unit_price' => '', 'category' => '']]) as $i => $item)
                                <fieldset class="quote-item">
                                    <legend>Ítem {{ $loop->iteration }}</legend>
                                    <div class="field"><label for="item-{{ $i }}-description">Descripción
                                            *</label><input id="item-{{ $i }}-description"
                                            name="items[{{ $i }}][description]"
                                            value="{{ $item['description'] }}" maxlength="500" required>
                                        @error('items.' . $i . '.description')
                                            <small class="error">{{ $message }}</small>
                                        @enderror
                                    </div>
                                    <div class="item-grid">
                                        <div class="field"><label for="item-{{ $i }}-quantity">Cantidad
                                                *</label><input id="item-{{ $i }}-quantity"
                                                name="items[{{ $i }}][quantity]"
                                                value="{{ $item['quantity'] }}" inputmode="decimal" required>
                                            @error('items.' . $i . '.quantity')
                                                <small class="error">{{ $message }}</small>
                                            @enderror
                                        </div>
                                        <div class="field"><label for="item-{{ $i }}-price">Precio unitario
                                                *</label><input id="item-{{ $i }}-price"
                                                name="items[{{ $i }}][unit_price]"
                                                value="{{ $item['unit_price'] }}" inputmode="decimal" required>
                                            @error('items.' . $i . '.unit_price')
                                                <small class="error">{{ $message }}</small>
                                            @enderror
                                        </div>
                                        <div class="field"><label
                                                for="item-{{ $i }}-category">Categoría</label><select
                                                id="item-{{ $i }}-category"
                                                name="items[{{ $i }}][category]">
                                                @foreach (['' => 'Sin categoría', 'material' => 'Material', 'mano de obra' => 'Mano de obra'] as $val => $label)
                                                    <option value="{{ $val }}" @selected(($item['category'] ?? '') === $val)>
                                                        {{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div><button type="button" class="text-button remove-item">Quitar ítem</button>
                                </fieldset>
                            @endforeach
                        </div>
                        <button type="button" id="add-item" class="button secondary">+ Agregar ítem</button><noscript>
                            <p>Para agregar o quitar ítems habilitá JavaScript. Podés guardar un presupuesto de un ítem
                                sin JavaScript.</p>
                        </noscript>
                    </section>
                    <section class="card"><x-field name="conditions" label="Condiciones del acuerdo (opcional)"
                            type="textarea" :value="$d['conditions'] ?? ''" maxlength="3000"
                            hint="Plazos, forma de pago y condiciones necesarias para comenzar." /></section>
                </div>
                <aside>
                    <section class="card sticky">
                        <h2>Importes y condiciones</h2>
                        <div class="field"><label for="currency">Moneda del presupuesto</label><select id="currency"
                                name="currency">
                                @foreach (['ARS', 'USD'] as $c)
                                    <option @selected(old('currency', $d['currency'] ?? $business->currency) === $c)>{{ $c }}</option>
                                @endforeach
                            </select>
                        </div><x-field name="discount" label="Descuento fijo (opcional)" :value="$d['discount'] ?? '0'"
                            inputmode="decimal" /><x-field name="tax_rate" label="Impuesto % (opcional)"
                            :value="$d['tax_rate'] ?? '0'" inputmode="decimal" /><x-field name="advance"
                            label="Anticipo solicitado (opcional)" :value="$d['advance'] ?? '0'" inputmode="decimal" />
                        <div class="calculation-note"><strong>Cómo calculamos</strong>
                            <p>Redondeamos cada ítem a centavos, restamos el descuento y aplicamos el impuesto al
                                importe restante. Redondeo: mitad hacia arriba.</p>
                        </div><button class="button full">Guardar y revisar totales →</button>
                        <p class="fineprint">Guardar no publica ni envía el presupuesto.</p>
                    </section>
                </aside>
            </div>
        </form>
    @endif
</x-layout>
