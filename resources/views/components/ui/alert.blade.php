@props(['severity' => 'info', 'title' => null])
<div {{ $attributes->class(['alert', 'alert--'.$severity]) }} role="{{ $severity === 'error' ? 'alert' : 'status' }}">@if($title)<strong>{{ $title }}</strong>@endif<div>{{ $slot }}</div></div>
