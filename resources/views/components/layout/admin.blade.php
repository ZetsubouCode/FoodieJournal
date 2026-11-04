@props(['title' => 'Admin'])
<x-layout.public :title="$title" :admin="true" :noindex="true">{{ $slot }}</x-layout.public>
