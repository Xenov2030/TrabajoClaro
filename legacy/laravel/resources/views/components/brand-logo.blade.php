@props(['variant'=>'light','tagline'=>false,'symbolOnly'=>false])
@php($variant=in_array($variant,['light','dark','mono'],true)?$variant:'light')
@php($suffix=$variant==='light'?'':'-'.$variant)
@php($base=$symbolOnly?'symbol':'logo-horizontal')
<span {{ $attributes->class(['brand-lockup','brand-lockup--'.$variant,'brand-lockup--symbol'=>$symbolOnly]) }}>
    <picture><source media="print" srcset="/brand/{{ $base }}-mono.svg"><img class="brand-logo-image" src="/brand/{{ $base }}{{ $suffix }}.svg" width="{{ $symbolOnly ? 64 : 316 }}" height="64" alt="{{ $symbolOnly?'Símbolo de TrabajoClaro':'TrabajoClaro' }}" decoding="async"></picture>
    @if($tagline && !$symbolOnly)<span class="brand-tagline">Cada trabajo en orden. Cada cobro, claro.</span>@endif
</span>
