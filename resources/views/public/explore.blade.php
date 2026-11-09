<x-layout.public title="Jelajah pengalaman" :canonical="url('explore')" :noindex="count(request()->query()) > 0">
    <div data-explore-shell>
    <x-discovery.curiosity-index :discovery="$discovery" :state="$state" />
    @include('public.explore-fragment')
    </div>
</x-layout.public>
