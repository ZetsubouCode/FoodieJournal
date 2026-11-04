@props(['title' => 'Tidak dapat memuat', 'description' => 'Coba kembali. Isian yang tersimpan tetap dipertahankan.'])
<x-ui.alert severity="error" :title="$title"><p>{{ $description }}</p>{{ $slot }}</x-ui.alert>
