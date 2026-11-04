@props(['name', 'label', 'options' => [], 'value' => null, 'placeholder' => null])
@php($key = trim(preg_replace('/\[([^\]]*)\]/', '.$1', $name), '.'))
@php($id = str_replace('.', '-', $key))
<div class="field"><label for="{{ $id }}">{{ $label }}</label><select id="{{ $id }}" name="{{ $name }}" aria-describedby="{{ $id }}-error" aria-invalid="{{ $errors->has($key) ? 'true' : 'false' }}" {{ $attributes }}>
    @if($placeholder !== null)<option value="">{{ $placeholder }}</option>@endif
    @foreach($options as $optionValue => $optionLabel)<option value="{{ $optionValue }}" @selected((string)old($key, $value) === (string)$optionValue)>{{ $optionLabel }}</option>@endforeach
</select><span class="field-error" id="{{ $id }}-error">{{ $errors->first($key) }}</span></div>
