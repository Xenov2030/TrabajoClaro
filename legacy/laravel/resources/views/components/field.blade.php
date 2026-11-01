@props(['name', 'label', 'value' => '', 'type' => 'text', 'hint' => null, 'required' => false])
<div class="field">
    <label for="{{ $name }}">{{ $label }}@if ($required)<span aria-hidden="true">&nbsp;*</span>@endif</label>
    @if ($type === 'textarea')
        <textarea id="{{ $name }}" name="{{ $name }}" {{ $required ? 'required' : '' }} {{ $attributes }}
            @if ($errors->has($name)) aria-invalid="true" aria-describedby="{{ $name }}-error" @endif>{{ old($name, $value) }}</textarea>
    @else<input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}"
            value="{{ $type === 'password' ? '' : old($name, $value) }}" {{ $required ? 'required' : '' }}
            {{ $attributes }}
            @if ($errors->has($name)) aria-invalid="true" aria-describedby="{{ $name }}-error" @endif>
    @endif
    @if ($hint)
        <small class="muted">{{ $hint }}</small>
    @endif @error($name)
    <small class="error" id="{{ $name }}-error">{{ $message }}</small>
@enderror
</div>
