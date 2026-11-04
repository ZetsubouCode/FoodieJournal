@props(['values', 'except' => []])
@foreach($values as $key => $value)@unless(in_array($key, $except))@foreach(is_array($value) ? $value : [$value] as $entry)<input type="hidden" name="{{ $key }}{{ is_array($value) ? '[]' : '' }}" value="{{ $entry }}">@endforeach @endunless @endforeach
