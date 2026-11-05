<x-layout.public title="Jelajah pengalaman" :canonical="url('explore')" :noindex="count(request()->query()) > 0">
    @include('public.explore-fragment')
    <x-domain.filter-panel :state="$state" :options="$options" :total="$results->total()" />
</x-layout.public>
