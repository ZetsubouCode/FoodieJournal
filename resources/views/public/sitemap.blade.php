@php echo '<?xml version="1.0" encoding="UTF-8"?>'; @endphp
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<url><loc>{{ url('/') }}</loc></url><url><loc>{{ url('/explore') }}</loc></url>
@foreach($visits as $visit)<url><loc>{{ url('/visit/'.$visit->slug) }}</loc><lastmod>{{ $visit->updated_at->toAtomString() }}</lastmod></url>@endforeach
@foreach($places as $place)<url><loc>{{ url('/place/'.$place->slug) }}</loc><lastmod>{{ $place->updated_at->toAtomString() }}</lastmod></url>@endforeach
</urlset>
