@props(['status' => null])
<span {{ $attributes->class(['badge', 'badge--'.$status]) }}>{{ $slot->isEmpty() ? $status : $slot }}</span>
