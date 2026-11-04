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
        foreach ([403, 404, 419, 429, 500, 503] as $code) {
            Route::get('/test-error-'.$code, fn () => abort($code, 'SECRET-INTERNAL-DETAIL'));
            $this->get('/test-error-'.$code)->assertStatus($code)->assertSee('Foodie')->assertDontSee('SECRET-INTERNAL-DETAIL')->assertDontSee('Stack trace');
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
