@props(['paginator'])
@if($paginator->hasPages())<nav class="pagination" aria-label="Halaman hasil">
    @if($paginator->previousPageUrl())<x-ui.button :href="$paginator->previousPageUrl()" rel="prev">Sebelumnya</x-ui.button>@endif
    <span>Halaman {{ $paginator->currentPage() }} dari {{ $paginator->lastPage() }}</span>
    @if($paginator->nextPageUrl())<x-ui.button :href="$paginator->nextPageUrl()" rel="next">Berikutnya</x-ui.button>@endif
</nav>@endif
