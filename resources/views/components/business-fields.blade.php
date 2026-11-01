@props(['b' => null])
<x-field name="{{ $b ? 'name' : 'business_name' }}" label="Nombre del negocio" :value="$b?->name ?? ''" required
    maxlength="120" />
<x-field name="industry" label="Rubro" :value="$b?->industry ?? ''" placeholder="Ej.: carpintería, instalaciones" required
    maxlength="100" />
<div class="form-grid">
    <div class="field"><label for="country">País</label><select id="country" name="country">
            @foreach (['AR' => 'Argentina', 'UY' => 'Uruguay', 'CL' => 'Chile', 'BO' => 'Bolivia', 'BR' => 'Brasil', 'PY' => 'Paraguay', 'PE' => 'Perú', 'CO' => 'Colombia', 'MX' => 'México', 'ES' => 'España', 'US' => 'Estados Unidos'] as $code => $name)
                <option value="{{ $code }}" @selected(old('country', $b?->country ?? 'AR') === $code)>{{ $name }}</option>
            @endforeach
        </select>
    </div>
    <div class="field"><label for="currency">Moneda predeterminada</label><select id="currency" name="currency">
            @foreach (['ARS', 'USD'] as $c)
                <option @selected(old('currency', $b?->currency ?? 'ARS') === $c)>{{ $c }}</option>
            @endforeach
        </select>
    </div>
</div>
<div class="field"><label for="timezone">Zona horaria</label><select id="timezone" name="timezone">
        @foreach (DateTimeZone::listIdentifiers() as $tz)
            <option @selected(old('timezone', $b?->timezone ?? 'America/Argentina/Buenos_Aires') === $tz)>{{ $tz }}</option>
        @endforeach
    </select></div>
