<div data-memory-package data-memory-id="{{ $memory['object_id'] }}" data-memory-count="{{ $memory['count'] }}" data-memory-title="{{ $memory['title'] }}">
    <template data-package-screen><x-discovery.memory :memory="$memory" /></template>
    <template data-package-insert>@if($memory['image'])<img src="{{ $memory['thumbnail'] }}" alt="" width="320" height="220">@else<span>Catatan Foodie.</span>@endif</template>
</div>
