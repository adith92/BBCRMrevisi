<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Sistem tema MODERN | CLASSIC (sumbu kedua, server-side via cookie crm-skin).
 * Menjamin: MODERN = default & tak berubah, CLASSIC opt-in, fallback aman,
 * dan switch route bekerja. Semua demi deploy aman selama fase theming.
 */
class SkinTest extends TestCase
{
    use RefreshDatabase;

    protected function user(string $role): User
    {
        return User::factory()->create(['role' => $role]);
    }

    /** @test */
    public function default_skin_is_modern(): void
    {
        $res = $this->actingAs($this->user('gm'))->get(route('dashboard'));

        $res->assertOk();
        $res->assertSee('data-skin="modern"', false);
    }

    /** @test */
    public function classic_cookie_renders_claude_design_shell(): void
    {
        $res = $this->actingAs($this->user('gm'))
            ->withUnencryptedCookie('crm-skin', 'classic')
            ->get(route('dashboard'));

        $res->assertOk();
        $res->assertSee('data-skin="classic"', false);
        $res->assertSee('Fleet Command', false); // brand-sub khas shell CLASSIC
    }

    /** @test */
    public function classic_falls_back_gracefully_for_unported_page(): void
    {
        // clients belum punya view classic → harus fallback ke view modern (200, tidak error)
        $res = $this->actingAs($this->user('gm'))
            ->withUnencryptedCookie('crm-skin', 'classic')
            ->get(route('clients.index'));

        $res->assertOk();
        $res->assertSee('data-skin="classic"', false);
    }

    /** @test */
    public function skin_switch_route_sets_cookie(): void
    {
        $res = $this->actingAs($this->user('gm'))->get(route('skin.switch', 'classic'));

        $res->assertPlainCookie('crm-skin', 'classic');
    }

    /** @test */
    public function invalid_skin_value_defaults_to_modern(): void
    {
        $res = $this->actingAs($this->user('gm'))->get(route('skin.switch', 'bogus'));

        $res->assertPlainCookie('crm-skin', 'modern');
    }

    /** @test */
    public function clients_uses_dedicated_classic_view_when_ported(): void
    {
        $res = $this->actingAs($this->user('gm'))
            ->withUnencryptedCookie('crm-skin', 'classic')
            ->get(route('clients.index'));

        $res->assertOk();
        $res->assertSee('data-skin="classic"', false);
        $res->assertSee('bb-table', false);          // markup khas view classic clients
        $res->assertSee('Revenue by Industry', false);
    }

    /** @test */
    public function clients_modern_view_is_untouched(): void
    {
        $res = $this->actingAs($this->user('gm'))->get(route('clients.index'));

        $res->assertOk();
        $res->assertSee('data-skin="modern"', false);
        $res->assertDontSee('bb-table', false);       // tidak memakai markup classic
    }

    /** @test */
    public function all_role_dashboards_render_under_classic(): void
    {
        foreach (['gm', 'manager', 'sales', 'operational', 'finance'] as $role) {
            $res = $this->actingAs($this->user($role))
                ->withUnencryptedCookie('crm-skin', 'classic')
                ->get(route('dashboard'));

            $res->assertOk();
            $res->assertSee('data-skin="classic"', false);
        }
    }
}
