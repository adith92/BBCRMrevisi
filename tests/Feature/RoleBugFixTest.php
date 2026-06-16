<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Vehicle;
use App\Models\Driver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleBugFixTest extends TestCase
{
    use RefreshDatabase;

    protected function user(string $role): User
    {
        return User::factory()->create(['role' => $role]);
    }

    /** @test */
    public function sales_dropdown_is_not_visible_for_sales_role(): void
    {
        $sales = $this->user('sales');

        $response = $this->actingAs($sales)
            ->get(route('opportunities.index'));

        $response->assertOk();
        $response->assertViewMissing('salesUsers');
        $response->assertDontSee('Semua Sales');
        $response->assertDontSee('name="sales_id"', false);
    }

    /** @test */
    public function sales_dropdown_is_visible_for_manager_role(): void
    {
        $manager = $this->user('manager');

        $response = $this->actingAs($manager)
            ->get(route('opportunities.index'));

        $response->assertOk();
        $response->assertViewHas('salesUsers');
        $response->assertSee('Semua Sales');
        $response->assertSee('name="sales_id"', false);
    }

    /** @test */
    public function sales_dropdown_is_visible_for_gm_role(): void
    {
        $gm = $this->user('gm');

        $response = $this->actingAs($gm)
            ->get(route('opportunities.index'));

        $response->assertOk();
        $response->assertViewHas('salesUsers');
        $response->assertSee('Semua Sales');
        $response->assertSee('name="sales_id"', false);
    }

    /** @test */
    public function fleet_detail_route_loads_correctly(): void
    {
        $gm = $this->user('gm');
        $vehicle = Vehicle::factory()->create([
            'plate_number' => 'B 1234 CD',
            'status' => 'available',
        ]);

        $response = $this->actingAs($gm)
            ->get(route('fleet.show', $vehicle->id));

        $response->assertOk();
        $response->assertSee($vehicle->plate_number);
    }

    /** @test */
    public function fleet_index_contains_clickable_detail_links(): void
    {
        $gm = $this->user('gm');
        $vehicle = Vehicle::factory()->create([
            'plate_number' => 'B 5678 EF',
            'status' => 'available',
        ]);

        $response = $this->actingAs($gm)
            ->get(route('fleet.index'));

        $response->assertOk();
        $response->assertSee(route('fleet.show', $vehicle->id));
        $response->assertSee($vehicle->plate_number);
    }

    /** @test */
    public function assign_modal_is_not_nested_inside_register_vehicle_modal(): void
    {
        $gm = $this->user('gm');

        $response = $this->actingAs($gm)
            ->get(route('fleet.index'));

        $response->assertOk();

        $dom = new \DOMDocument();
        $previousUseInternalErrors = libxml_use_internal_errors(true);
        $dom->loadHTML($response->getContent());
        libxml_clear_errors();
        libxml_use_internal_errors($previousUseInternalErrors);

        $xpath = new \DOMXPath($dom);
        $assignModal = $xpath->query('//*[@x-show="showAssignModal"]')->item(0);

        $this->assertNotNull($assignModal, 'Assign modal container must be rendered on Fleet index.');

        $ancestor = $assignModal->parentNode;
        while ($ancestor instanceof \DOMElement) {
            $this->assertNotSame(
                'showCreateModal',
                $ancestor->getAttribute('x-show'),
                'Assign modal must not be nested inside the Register Vehicle modal wrapper.'
            );

            $ancestor = $ancestor->parentNode;
        }
    }

    /** @test */
    public function driver_detail_route_loads_correctly(): void
    {
        $gm = $this->user('gm');
        $driver = Driver::factory()->create([
            'name' => 'Joko Susilo',
            'status' => 'Available',
        ]);

        $response = $this->actingAs($gm)
            ->get(route('drivers.show', $driver->id));

        $response->assertOk();
        $response->assertSee($driver->name);
    }

    /** @test */
    public function driver_index_contains_clickable_detail_links(): void
    {
        $gm = $this->user('gm');
        $driver = Driver::factory()->create([
            'name' => 'Budi Santoso',
            'status' => 'Available',
        ]);

        $response = $this->actingAs($gm)
            ->get(route('drivers.index'));

        $response->assertOk();
        $response->assertSee(route('drivers.show', $driver->id));
        $response->assertSee($driver->name);
    }

    /** @test */
    public function operational_user_can_view_opportunity_detail(): void
    {
        $operational = $this->user('operational');
        $opportunity = \App\Models\Opportunity::factory()->create();

        $response = $this->actingAs($operational)
            ->get(route('opportunities.show', $opportunity->id));

        $response->assertOk();
    }

    /** @test */
    public function operational_user_can_access_create_activity_page(): void
    {
        $operational = $this->user('operational');
        $opportunity = \App\Models\Opportunity::factory()->create();

        $response = $this->actingAs($operational)
            ->get(route('activities.create', ['opportunity_id' => $opportunity->id]));

        $response->assertOk();
    }

    /** @test */
    public function operational_user_can_store_activity(): void
    {
        $operational = $this->user('operational');
        $opportunity = \App\Models\Opportunity::factory()->create();

        $response = $this->actingAs($operational)
            ->post(route('activities.store'), [
                'type' => 'meeting',
                'subject' => 'Meeting with client by Ops',
                'activity_date' => now()->format('Y-m-d H:i:s'),
                'opportunity_id' => $opportunity->id,
                'client_id' => $opportunity->client_id,
            ]);

        $response->assertRedirect(route('opportunities.show', $opportunity->id));

        $this->assertDatabaseHas('activity_logs', [
            'subject' => 'Meeting with client by Ops',
            'opportunity_id' => $opportunity->id,
            'sales_id' => $operational->id
        ]);
    }

    /** @test */
    public function opportunity_with_long_term_product_id_is_included_in_pending_assignments(): void
    {
        $operational = $this->user('operational');
        
        // Ensure Long Term category and product exist
        $category = \App\Models\ProductCategory::factory()->create([
            'id' => 2,
            'name' => 'Long Term',
            'type' => 'long_term',
        ]);
        $product = \App\Models\Product::factory()->create([
            'id' => 4,
            'product_category_id' => 2,
            'name' => 'Mobil Long Term',
        ]);
        
        $opp = \App\Models\Opportunity::factory()->create([
            'product_id' => $product->id,
            'title' => 'Ensured Sales Revenue Deal — 3 unit',
            'stage' => 'won',
            'products' => null,
        ]);
        
        $response = $this->actingAs($operational)
            ->get(route('fleet.index', ['status' => 'approval_pending']));
            
        $response->assertOk();
        $response->assertSee($opp->title);
    }

    /** @test */
    public function fleet_index_displays_required_driver_count_for_pending_assignments(): void
    {
        $operational = $this->user('operational');

        $opp = \App\Models\Opportunity::factory()->create([
            'title' => 'Weekend Leisure Fleet — 4 unit',
            'stage' => 'won',
            'products' => [
                [
                    'name' => 'Mobil Long Term',
                    'category' => 'Long Term',
                    'quantity' => 4,
                    'price' => 1000000,
                ],
                [
                    'name' => 'Supir',
                    'category' => 'Service',
                    'quantity' => 2,
                    'price' => 250000,
                ],
            ],
        ]);

        $response = $this->actingAs($operational)
            ->get(route('fleet.index'));

        $response->assertOk();
        $response->assertSee('Supir Required:');
        $response->assertSee('>2</strong>', false);
        $response->assertSee('"required_drivers":2', false);
    }

    /** @test */
    public function fleet_assign_script_clears_assign_opp_query_param_after_save(): void
    {
        $gm = $this->user('gm');

        $response = $this->actingAs($gm)
            ->get(route('fleet.index', ['assign_opp' => 99]));

        $response->assertOk();
        $response->assertSee("url.searchParams.delete('assign_opp');", false);
        $response->assertSee('window.location.href = url.toString();', false);
    }
}
