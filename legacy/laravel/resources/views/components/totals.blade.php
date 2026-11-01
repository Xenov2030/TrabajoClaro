@props(['s'])<dl class="totals">
    @foreach (['subtotal_minor' => 'Subtotal', 'discount_minor' => 'Descuento fijo', 'tax_minor' => 'Impuesto (' . ($s['tax_rate'] ?? '0') . '%)', 'total_minor' => 'Total acordado', 'advance_minor' => 'Anticipo solicitado'] as $key => $label)
        <div @class(['total-line' => $key === 'total_minor'])>
            <dt>{{ $label }}</dt>
            <dd>{{ \App\Services\Money::display($s[$key], $s['currency']) }}</dd>
        </div>
    @endforeach
</dl>
