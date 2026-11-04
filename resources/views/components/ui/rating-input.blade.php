@props(['name', 'label', 'value' => null])
@php($key = trim(preg_replace('/\[([^\]]*)\]/', '.$1', $name), '.'))
<div class="field"><label for="{{ str_replace('.', '-', $key) }}">{{ $label }}</label>
<select name="{{ $name }}" id="{{ str_replace('.', '-', $key) }}" aria-invalid="{{ $errors->has($key) ? 'true' : 'false' }}" aria-describedby="{{ str_replace('.', '-', $key) }}-error" {{ $attributes }}><option value="">Belum Dinilai</option>@foreach(range(2, 10) as $half)<option value="{{ $half / 2 }}" @selected((string)old($key, $value) === (string)($half / 2))>{{ number_format($half / 2, 1, ',', '.') }}</option>@endforeach</select><span class="field-error" id="{{ str_replace('.', '-', $key) }}-error">{{ $errors->first($key) }}</span></div>
