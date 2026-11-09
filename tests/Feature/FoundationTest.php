<?php

namespace Tests\Feature;

use App\Support\Presentation;
use Illuminate\Support\HtmlString;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

class FoundationTest extends TestCase
{
    public function test_shared_shell_renders_and_has_accessible_feedback(): void
    {
        $this->withoutVite();
        view()->share('errors', new ViewErrorBag);
        $html = view('components.layout.public', ['slot' => new HtmlString('<h1>Catatan</h1>')])->render();
        $this->assertStringContainsString('lang="id"', $html);
        $this->assertStringContainsString('Lewati ke isi', $html);
        $this->assertStringContainsString('aria-labelledby="confirm-dialog-title"', $html);
        $this->assertStringNotContainsString('Register', $html);
        $this->assertStringNotContainsString('admin/login', $html);
    }

    public function test_time_and_nullable_presentation_preserve_meaning(): void
    {
        $this->assertSame('2026-11-03 17:15', Presentation::utcFromLocal('2026-11-04T00:15')->format('Y-m-d H:i'));
        $this->assertSame('3,8', Presentation::rating(3.75));
        $this->assertSame('Belum Dinilai', Presentation::rating(null));
        $this->assertSame('Rp0', Presentation::price(0));
        $this->assertSame('Harga belum dicatat', Presentation::price(null));
    }

    public function test_saved_feedback_is_rendered_once_without_a_duplicate_toast(): void
    {
        $this->withoutVite();
        $this->withSession(['success' => 'Catatan QA tersimpan.']);
        view()->share('errors', new ViewErrorBag);
        $html = view('components.layout.public', ['slot' => new HtmlString('<h1>Catatan</h1>')])->render();
        $this->assertSame(1, substr_count($html, 'Catatan QA tersimpan.'));
        $this->assertStringNotContainsString('data-message=', $html);
    }
}
