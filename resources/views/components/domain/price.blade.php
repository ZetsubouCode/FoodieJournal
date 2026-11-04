@props(['value' => null, 'label'])
<span class="numeric">{{ $label }}: {{ \App\Support\Presentation::price($value) }}</span>
