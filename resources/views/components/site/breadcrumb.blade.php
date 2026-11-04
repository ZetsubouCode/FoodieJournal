@props(['items' => []])
<nav aria-label="Jejak halaman" class="breadcrumb"><a href="{{ url('/') }}">Beranda</a>@foreach($items as $label => $href)<span aria-hidden="true">›</span>@if($href)<a href="{{ $href }}">{{ $label }}</a>@else<span aria-current="page">{{ $label }}</span>@endif @endforeach</nav>
