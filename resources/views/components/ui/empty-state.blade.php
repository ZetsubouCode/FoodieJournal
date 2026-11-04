@props(['title' => 'Belum ada catatan', 'description' => null, 'href' => null, 'action' => 'Jelajah pengalaman'])
<div class="empty-state"><h2>{{ $title }}</h2>@if($description)<p>{{ $description }}</p>@endif @if($href)<x-ui.button :href="$href">{{ $action }}</x-ui.button>@endif {{ $slot }}</div>
