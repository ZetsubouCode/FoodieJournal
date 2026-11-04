<x-layout.admin title="Kunjungan">
    <div class="split"><h1>Kunjungan</h1><x-ui.button href="{{ url('admin/visit/create') }}" variant="primary">Tambah Kunjungan</x-ui.button></div>
    <form method="get" class="toolbar">
        <label class="field">Status<select name="status"><option value="">Semua status</option>@foreach(['draft', 'private', 'published'] as $status)<option @selected(request('status') === $status)>{{ $status }}</option>@endforeach</select></label>
        <label class="field">Tempat<select name="place_id"><option value="">Semua tempat</option>@foreach($places as $place)<option value="{{ $place->id }}" @selected(request('place_id') == $place->id)>{{ $place->name }}</option>@endforeach</select></label>
        <x-ui.field name="from" label="Dari tanggal" type="date" :value="request('from')" /><x-ui.field name="to" label="Sampai tanggal" type="date" :value="request('to')" /><x-ui.button type="submit">Terapkan</x-ui.button>
    </form>
    @forelse($visits as $visit)<article class="item-entry split"><div><x-ui.badge :status="$visit->status" /><h2><a href="{{ url('admin/visit/'.$visit->id.'/edit') }}">{{ $visit->display_title }}</a></h2><p class="muted">{{ \App\Support\Presentation::localTime($visit->visited_at) }} · <x-domain.rating :value="$visit->overall_rating" /></p></div><x-ui.button href="{{ url('admin/visit/'.$visit->id.'/preview') }}">Pratinjau</x-ui.button></article>@empty<x-ui.empty-state title="Belum ada kunjungan yang cocok" />@endforelse
    <x-ui.pagination :paginator="$visits" />
</x-layout.admin>
