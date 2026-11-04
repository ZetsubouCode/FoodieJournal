@props(['name', 'label', 'type' => 'text', 'value' => null, 'help' => null, 'required' => false, 'textarea' => false, 'id' => null])
@php($key = trim(preg_replace('/\[([^\]]*)\]/', '.$1', $name), '.'))
@php($id = $id ?? str_replace('.', '-', $key))
<div class="field">
    <label for="{{ $id }}">{{ $label }}@if($required) <span aria-hidden="true">*</span>@endif</label>
    @if($textarea)
    <textarea id="{{ $id }}" name="{{ $name }}" @required($required) aria-describedby="{{ $id }}-help {{ $id }}-error" aria-invalid="{{ $errors->has($key) ? 'true' : 'false' }}" {{ $attributes }}>{{ old($key, $value) }}</textarea>
    @else
    <input id="{{ $id }}" name="{{ $name }}" type="{{ $type }}" value="{{ $type === 'password' ? '' : old($key, $value) }}" @required($required) aria-describedby="{{ $id }}-help {{ $id }}-error" aria-invalid="{{ $errors->has($key) ? 'true' : 'false' }}" {{ $attributes }}>
    @endif
    <small id="{{ $id }}-help">{{ $help }}</small>
    <span id="{{ $id }}-error" class="field-error">{{ $errors->first($key) }}</span>
</div>
