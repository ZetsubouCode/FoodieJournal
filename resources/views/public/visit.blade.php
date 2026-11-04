<x-layout.public :title="$visit->display_title" :description="\Illuminate\Support\Str::limit($visit->experience ?: $visit->display_title, 160)" :noindex="$preview" :admin="$preview" :image="!$preview && $visit->coverMedia?->variant('hero') ? url('media/'.$visit->coverMedia->uuid.'/hero') : null">
    @if($preview)<x-ui.alert title="Pratinjau admin">Status: {{ $visit->status }}. Ini pratinjau tersimpan; halaman ini tidak dipublikasikan otomatis. <a href="{{ url('admin/visit/'.$visit->id.'/edit') }}">Kembali ke editor</a></x-ui.alert>@endif
    <x-site.breadcrumb :items="['Jelajah' => url('explore'), $visit->display_title => null]" />
    <header class="hero {{ $visit->coverMedia?->variant('hero') ? '' : 'hero--text' }}">
        @if($visit->coverMedia?->variant('hero'))<x-domain.image :media="$visit->coverMedia" variant="hero" :priority="true" />@endif
        <div class="hero-copy"><div class="hero-meta"><time datetime="{{ $visit->visited_at?->toIso8601String() }}">{{ \App\Support\Presentation::localTime($visit->visited_at) }}</time><span>{{ $visit->place->city_name }}</span></div><h1>{{ $visit->display_title }}</h1>
            <p><a href="{{ url('place/'.$visit->place->slug) }}">{{ $visit->place->name }}</a></p><p><x-domain.rating :value="$visit->overall_rating" label="Keseluruhan" /></p>
            @if($visit->recommendations->isNotEmpty())<div class="cluster">@foreach($visit->recommendations as $term)<a class="chip" href="{{ url('explore').'?'.http_build_query(['recommendation' => [$term->slug]]) }}">{{ $term->name }}</a>@endforeach</div>@endif
        </div>
    </header>
    @if($visit->experience)<section class="section reading"><h2>Cerita kunjungan</h2><p class="prose">{{ $visit->experience }}</p></section>@endif
    @if($visit->items->isNotEmpty())<section class="section"><h2>Hidangan yang dicoba</h2>
        @foreach($visit->items as $item)<article class="item-entry reading" id="hidangan-{{ $item->id }}"><h3>{{ $item->menu_name }}</h3>
            <div class="cluster"><x-domain.price :value="$item->unit_price_idr" label="Harga satuan saat dipesan" />@if($item->quantity !== null)<span class="numeric">Jumlah: {{ (float)$item->quantity }}</span>@endif<x-domain.rating :value="$item->taste_rating !== null ? (float)$item->taste_rating : null" label="Rasa" /></div>
            @if($item->foodType)<p class="muted">Jenis hidangan: <a href="{{ url('explore').'?'.http_build_query(['tab' => 'food', 'food_type' => [$item->foodType->slug]]) }}">{{ $item->foodType->name }}</a></p>@endif
            @if($item->cuisines->isNotEmpty())<p>Asal/tradisi kuliner: {{ $item->cuisines->pluck('name')->join(', ') }}</p>@endif
            @if($item->styles->isNotEmpty())<p>Teknik memasak: {{ $item->styles->pluck('name')->join(', ') }}</p>@endif
            @if($item->notes)<p class="prose">{{ $item->notes }}</p>@endif
            @if($item->media->isNotEmpty())<div class="gallery">@foreach($item->media as $photo)@if($photo->variant('thumbnail'))<a href="#galeri" aria-label="Ke galeri: {{ $photo->alt_text ?: 'foto hidangan' }}"><x-domain.image :media="$photo" variant="thumbnail" /></a>@endif @endforeach</div>@endif
            <details><summary>Bahan & informasi alergen</summary>
                @if($item->ingredients->isNotEmpty())<p>Bahan yang diketahui (bukan resep lengkap):</p><ul>@foreach($item->ingredients as $term)<li>{{ $term->name }}@if($term->pivot->knowledge_source) · Sumber: {{ $term->pivot->knowledge_source }}@endif</li>@endforeach</ul>@endif
                @if($item->allergens->isEmpty())<p>Informasi belum tersedia untuk alergen.</p>@else<ul>@foreach($item->allergens as $term)<li><strong>{{ $term->label_id }}</strong>: {{ $term->pivot->exposure_status === 'contains' ? 'Tercatat mengandung' : 'Berpotensi mengandung' }}. Sumber: {{ $term->pivot->evidence_source }}@if($term->pivot->notes) · {{ $term->pivot->notes }}@endif</li>@endforeach</ul><p class="muted">Informasi alergen lain mungkin belum tersedia.</p>@endif
                <p class="muted">{{ config('foodie.allergen_disclaimer') }}</p>
            </details>
        </article>@endforeach
    </section>@endif
    <section class="section reading"><h2>Catatan penilaian & biaya</h2><p><x-domain.price :value="$visit->total_spent_idr" label="Total pengeluaran kunjungan" /></p><p class="muted">Total dicatat tersendiri; dapat berbeda dari penjumlahan harga hidangan.</p>
        @if($visit->taste_rating !== null)<p><x-domain.rating :value="$visit->taste_rating" label="Rasa, rata-rata hidangan dinilai" /></p>@endif
        @foreach(['service' => 'Pelayanan', 'atmosphere' => 'Suasana', 'cleanliness' => 'Kebersihan', 'value' => 'Sepadan dengan biaya'] as $key => $label)@if($visit->{$key.'_review'} || $visit->{$key.'_rating'} !== null)<div class="section"><h3>{{ $label }}</h3>@if($visit->{$key.'_rating'} !== null)<p><x-domain.rating :value="(float)$visit->{$key.'_rating'}" /></p>@endif @if($visit->{$key.'_review'})<p class="prose">{{ $visit->{$key.'_review'} }}</p>@endif</div>@endif @endforeach
    </section>
    @if($visit->media->isNotEmpty())<section class="section" id="galeri"><h2>Galeri kunjungan</h2><x-domain.gallery :media="$visit->media" /></section>@endif
    @if($visit->conclusion)<section class="section reading"><h2>Kesimpulan</h2><p class="prose">{{ $visit->conclusion }}</p></section>@endif
    @if($visit->tags->isNotEmpty())<div class="cluster">@foreach($visit->tags as $term)<a class="chip" href="{{ url('explore').'?'.http_build_query(['tag' => [$term->slug]]) }}">{{ $term->name }}</a>@endforeach</div>@endif
    <div class="action-group section">@unless($preview)<x-ui.button href="{{ url('place/'.$visit->place->slug) }}">Lihat tempat</x-ui.button>@endunless @if($visit->place->maps_url)<x-ui.button href="{{ $visit->place->maps_url }}" rel="noopener noreferrer" target="_blank">Buka Google Maps</x-ui.button>@endif</div>
    @if($related->isNotEmpty())<section class="section"><h2>Kunjungan lain ke tempat ini</h2><div class="results">@foreach($related as $other)<x-domain.visit-card :visit="$other" />@endforeach</div></section>@endif
</x-layout.public>
