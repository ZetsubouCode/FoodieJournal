<x-layout.admin title="Masuk admin">
    <div class="reading section"><h1>Lanjutkan catatan kulinermu.</h1><p class="muted">Masuk untuk mengelola jurnal Foodie.</p>
    <form method="post" action="{{ url('admin/login') }}" class="stack">@csrf
        <x-ui.field name="email" label="Email" type="email" required autocomplete="username" />
        <x-ui.field name="password" label="Kata sandi" type="password" required autocomplete="current-password" />
        <x-ui.button type="submit" variant="primary">Masuk</x-ui.button>
    </form></div>
</x-layout.admin>
