@props(['name', 'label', 'kind', 'selected' => [], 'multiple' => false, 'create' => true, 'required' => false])
@php($key = trim(preg_replace('/\[([^\]]*)\]/', '.$1', $name), '.'))
@php($id = str_replace('.', '-', $key))
@php($selected = old(trim(preg_replace('/\[([^\]]*)\]/', '.$1', $name), '.'), $selected))
<div class="field" data-combobox data-name="{{ $name }}" data-kind="{{ $kind }}" data-multiple="{{ $multiple ? 'true' : 'false' }}" data-selected="{{ json_encode($selected) }}" data-endpoint="{{ url('admin/suggestions/'.$kind) }}">
    <label for="{{ $id }}">{{ $label }}@if($required) <span aria-hidden="true">*</span>@endif</label>
    <div class="combobox">
        <input id="{{ $id }}" type="search" role="combobox" aria-autocomplete="list" aria-expanded="false" aria-controls="{{ $id }}-list" aria-describedby="{{ $id }}-status {{ $id }}-error" aria-invalid="{{ $errors->has($key) ? 'true' : 'false' }}" autocomplete="off" placeholder="Cari {{ mb_strtolower($label) }}…" data-combo-input>
        <div id="{{ $id }}-list" class="combobox-list" role="listbox" hidden data-combo-list></div>
    </div>
    <div class="selected-options" data-combo-selected></div>
    <small id="{{ $id }}-status" role="status" data-combo-status></small>
    <span class="field-error" id="{{ $id }}-error">{{ $errors->first($key) }}</span>
    @if($create && $kind !== 'allergen')<x-ui.button type="button" variant="tertiary" data-combo-create>Tambah {{ mb_strtolower($label) }}</x-ui.button>@endif
    <noscript><p>Aktifkan JavaScript untuk mencari dan memilih {{ mb_strtolower($label) }}.</p></noscript>
</div>
