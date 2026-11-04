<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class SystemSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_branded_error_pages_do_not_expose_internal_details(): void
    {
        $approvedCopy = [
            403 => ['Halaman ini tidak bisa diakses', 'Kamu tidak memiliki akses untuk melihat halaman ini.'],
            404 => ['Halaman yang dicari tidak ditemukan', 'Mungkin alamatnya berubah atau halaman tersebut sudah tidak tersedia.'],
            419 => ['Sesi telah berakhir', 'Sesi sebelumnya sudah tidak berlaku. Silakan mulai kembali untuk melanjutkan.'],
            429 => ['Terlalu banyak permintaan', 'Ada terlalu banyak permintaan dalam waktu singkat. Silakan coba lagi nanti.'],
            500 => ['Jurnal sedang mengalami gangguan', 'Terjadi kendala saat memuat halaman ini. Silakan coba lagi beberapa saat.'],
            503 => ['Foodie sementara tidak tersedia', 'Jurnal sedang tidak dapat diakses. Silakan kembali beberapa saat lagi.'],
        ];

        foreach ([403, 404, 419, 429, 500, 503] as $code) {
            Route::get('/test-error-'.$code, fn () => abort($code, 'SECRET-INTERNAL-DETAIL'));
            $this->get('/test-error-'.$code)->assertStatus($code)
                ->assertSee($approvedCopy[$code][0])
                ->assertSee($approvedCopy[$code][1])
                ->assertSee('<span class="error-code-text">'.$code.'</span>', false)
                ->assertSee('noindex, nofollow')
                ->assertSee('assets/css/error.css')
                ->assertDontSee('SECRET-INTERNAL-DETAIL')
                ->assertDontSee('dapur kami')
                ->assertDontSee('Stack trace');
            $this->getJson('/test-error-'.$code)->assertStatus($code)
                ->assertHeader('Content-Type', 'application/json')
                ->assertDontSee('error-scene')
                ->assertDontSee('SECRET-INTERNAL-DETAIL');
        }

        $this->assertFileExists(public_path('assets/css/error.css'));
        $this->assertFileExists(public_path('assets/images/error/cinematic-kitchen.webp'));
    }

    public function test_error_recovery_links_are_distinct_and_retry_only_safe_requests(): void
    {
        config()->set('app.debug', false);
        Route::match(['GET', 'POST'], '/test-failure', fn () => throw new \RuntimeException('SECRET-DB-PATH'));

        $this->get('/test-failure?from=journal')->assertStatus(500)
            ->assertSee('Coba lagi')
            ->assertSee('href="'.url('/test-failure?from=journal').'"', false)
            ->assertSee('href="'.url('/').'"', false)
            ->assertDontSee('SECRET-DB-PATH');
        $this->post('/test-failure')->assertStatus(500)
            ->assertDontSee('Coba lagi')
            ->assertSee('Ke beranda')
            ->assertSee('Jelajahi catatan')
            ->assertDontSee('SECRET-DB-PATH');

        $this->get('/test-error-404')->assertStatus(404)
            ->assertSee('href="/explore"', false)
            ->assertSee('href="/"', false);
    }

    public function test_real_maintenance_middleware_uses_the_branded_page_and_keeps_json(): void
    {
        $originalStorage = app()->storagePath();
        $isolatedStorage = sys_get_temp_dir().'/foodie-maintenance-'.bin2hex(random_bytes(6));
        mkdir($isolatedStorage.'/framework', 0700, true);
        app()->useStoragePath($isolatedStorage);
        try {
            $this->artisan('down', ['--retry' => 120])->assertSuccessful();
            $this->get('/explore')->assertStatus(503)
                ->assertHeader('Retry-After', '120')
                ->assertSee('Foodie sementara tidak tersedia')
                ->assertSee('<span class="error-code-text">503</span>', false);
            $this->getJson('/explore')->assertStatus(503)
                ->assertHeader('Retry-After', '120')
                ->assertHeader('Content-Type', 'application/json')
                ->assertDontSee('error-scene');
        } finally {
            $this->artisan('up')->assertSuccessful();
            app()->useStoragePath($originalStorage);
            foreach (['down', 'maintenance.php'] as $file) {
                if (is_file($isolatedStorage.'/framework/'.$file)) {
                    unlink($isolatedStorage.'/framework/'.$file);
                }
            }
            rmdir($isolatedStorage.'/framework');
            rmdir($isolatedStorage);
        }
    }

    public function test_csrf_is_enforced_and_security_headers_present(): void
    {
        $this->app['env'] = 'production';
        $this->post('/admin/login', ['email' => 'a@example.test', 'password' => 'anything'])->assertStatus(419)->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->get('/explore')->assertOk()->assertHeader('X-Frame-Options', 'DENY')->assertHeader('Content-Security-Policy');
    }

    public function test_preview_is_protected_and_public_metadata_canonical_is_stable(): void
    {
        $this->get('/admin/visit/123/preview')->assertRedirect('/admin/login');
        $this->get('/explore?q=abc')->assertOk()->assertSee('noindex,follow')->assertSee('rel="canonical" href="'.url('explore').'"', false);
        $this->get('/robots.txt')->assertOk()->assertSee('Disallow: /admin');
    }

    public function test_first_owner_cli_prompts_and_refuses_a_second_owner(): void
    {
        $password = 'Aa9'.bin2hex(random_bytes(10));
        $this->artisan('foodie:admin')->expectsQuestion('Nama', 'Pemilik Uji')->expectsQuestion('Email', 'owner@example.test')->expectsQuestion('Kata sandi (minimal 12 karakter)', $password)->expectsQuestion('Ulangi kata sandi', $password)->assertSuccessful();
        $this->assertDatabaseHas('user', ['email' => 'owner@example.test', 'is_admin' => true]);
        $this->artisan('foodie:admin')->assertFailed();
        $this->assertDatabaseCount('user', 1);
    }
}
