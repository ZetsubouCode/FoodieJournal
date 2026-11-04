<x-layout.admin title="Tempat">
    <div class="split"><h1>Tempat</h1><x-ui.button href="{{ url('admin/place/create') }}" variant="primary">Tambah tempat</x-ui.button></div>
    <form method="get" class="toolbar"><div class="search"><x-ui.field name="q" label="Cari tempat" :value="request('q')" /></div><x-ui.button type="submit">Cari</x-ui.button></form>
    @forelse($places as $place)<div class="split item-entry"><div><h2><a href="{{ url('admin/place/'.$place->id.'/edit') }}">{{ $place->name }}</a></h2><p class="muted">{{ $place->city_name ?: 'Lokasi belum dicatat' }} · {{ $place->visits_count }} kunjungan</p></div></div>@empty<x-ui.empty-state title="Belum ada tempat" description="Nama saja cukup. Alamat dan lokasi dapat dilengkapi nanti." />@endforelse
    <x-ui.pagination :paginator="$places" />
</x-layout.admin>
