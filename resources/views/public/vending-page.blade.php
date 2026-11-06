<div data-vending-page data-page="{{ $vending['page'] }}" data-pages="{{ $vending['pages'] }}" data-total="{{ $vending['total'] }}">
    <x-discovery.vending-slots :vending="$vending" />
    <template data-vending-empty><x-discovery.vending-preview /></template>
</div>
