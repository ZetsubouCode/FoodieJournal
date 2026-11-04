<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Cuisine;
use App\Models\Place;
use App\Models\User;
use App\Models\Visit;
use App\Models\VisitItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function admin(): User
    {
        $user = User::factory()->create();
        $user->is_admin = true;
        $user->save();

        return $user;
    }

    public function test_admin_routes_registration_and_non_admin_permissions(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
        $this->get('/register')->assertNotFound();
        $this->actingAs(User::factory()->create())->get('/admin')->assertForbidden();
        $admin = $this->admin();
        $this->actingAs($admin);
        foreach (['/admin', '/admin/visit', '/admin/visit/create', '/admin/place', '/admin/place/create', '/admin/taxonomy', '/admin/taxonomy/cuisine', '/admin/taxonomy/allergen'] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_quick_entry_is_idempotent_and_preserves_timezone_then_editor_saves_items(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin);
        $this->postJson('/admin/place', ['name' => 'Warung Pagi'])->assertCreated();
        $place = Place::first();
        $token = (string) Str::uuid();
        $data = ['place_id' => $place->id, 'visited_at' => '2026-11-04T00:15', 'creation_token' => $token, 'intent' => 'draft'];
        $this->post('/admin/visit', $data)->assertSessionHasNoErrors();
        $this->post('/admin/visit', $data)->assertSessionHasNoErrors();
        $this->assertSame(1, Visit::count());
        $visit = Visit::first();
        $this->assertSame('2026-11-03 17:15', $visit->visited_at->format('Y-m-d H:i'));
        $this->get('/admin/visit/'.$visit->id.'/edit')->assertOk()->assertSee('Harga satuan', false, false);
        $cuisine = Cuisine::create(['name' => 'Thai']);
        $edit = [...$data, 'editor' => 1, 'version' => 1, 'intent' => 'published', 'total_spent_idr' => 60000, 'items' => [['menu_name' => 'Mie kuah', 'quantity' => 1, 'unit_price_idr' => 25000, 'taste_rating' => 4.5, 'cuisines' => [$cuisine->id]]]];
        $this->put('/admin/visit/'.$visit->id, $edit)->assertSessionHasNoErrors();
        $this->assertSame('published', $visit->fresh()->status);
        $this->assertSame(60000, $visit->fresh()->total_spent_idr);
        $this->assertSame(25000, VisitItem::first()->unit_price_idr);
        $this->get('/admin/visit/'.$visit->id.'/edit')->assertOk();
        $this->put('/admin/visit/'.$visit->id, $edit)->assertSessionHasErrors('version');
    }

    public function test_publish_gate_and_owner_and_item_tampering(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin);
        $visit = Visit::factory()->create(['user_id' => $admin->id, 'experience' => null]);
        $base = ['place_id' => $visit->place_id, 'visited_at' => '', 'version' => 1, 'intent' => 'published'];
        $this->put('/admin/visit/'.$visit->id, $base)->assertSessionHasErrors(['visited_at', 'experience']);
        $this->assertSame('draft', $visit->fresh()->status);
        $other = Visit::factory()->create();
        $this->get('/admin/visit/'.$other->id.'/edit')->assertForbidden();
        $this->get('/admin/visit/'.$other->id.'/preview')->assertForbidden();
        $item = VisitItem::factory()->for($other)->create();
        $this->put('/admin/visit/'.$visit->id, [...$base, 'intent' => 'draft', 'editor' => 1, 'items' => [['id' => $item->id, 'menu_name' => 'Tamper']]])->assertSessionHasErrors('items.0.id');
        $this->assertNotSame('Tamper', $item->fresh()->menu_name);
    }

    public function test_taxonomy_hierarchy_duplicates_and_reference_protection(): void
    {
        $this->actingAs($this->admin());
        $a = Category::create(['name' => 'Makanan']);
        $b = Category::create(['name' => 'Mie', 'parent_id' => $a->id]);
        $this->put('/admin/taxonomy/category/'.$a->id, ['name' => 'Makanan', 'parent_id' => $b->id])->assertSessionHasErrors('parent_id');
        $this->delete('/admin/taxonomy/category/'.$a->id)->assertSessionHasErrors('taxonomy');
        $this->postJson('/admin/taxonomy/food-type', ['name' => 'Mie'])->assertUnprocessable();
        $this->postJson('/admin/taxonomy/allergen', ['name' => 'Any'])->assertForbidden();
        $this->postJson('/admin/place', ['name' => 'X', 'maps_url' => 'javascript:alert(1)'])->assertUnprocessable();
    }

    public function test_login_logout_and_rate_limiting(): void
    {
        $admin = $this->admin();
        $this->post('/admin/login', ['email' => $admin->email, 'password' => 'password'])->assertRedirect('/admin');
        $this->assertAuthenticatedAs($admin);
        $this->post('/admin/logout')->assertRedirect('/admin/login');
        $this->assertGuest();
        for ($i = 0; $i < 5; $i++) {
            $this->post('/admin/login', ['email' => 'missing@example.test', 'password' => 'bad'])->assertSessionHasErrors('email');
        }
        $this->post('/admin/login', ['email' => 'missing@example.test', 'password' => 'bad'])->assertStatus(429);
    }

    public function test_public_resource_requests_do_not_consume_login_rate_budget(): void
    {
        for ($i = 0; $i < 12; $i++) {
            $this->get('/media/'.Str::uuid().'/card')->assertNotFound();
        }
        $admin = $this->admin();
        $this->post('/admin/login', ['email' => $admin->email, 'password' => 'password'])->assertRedirect('/admin');
    }
}
