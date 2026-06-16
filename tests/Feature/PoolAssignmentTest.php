<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Vehicle;
use App\Models\Driver;
use App\Models\Opportunity;
use App\Models\Pool;
use App\Models\ProductCategory;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PoolAssignmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setupDbData()
    {
        $poolJakarta = Pool::create([
            'name' => 'Pool Jakarta',
            'location' => 'Jakarta',
            'capacity' => 10
        ]);

        $poolSurabaya = Pool::create([
            'name' => 'Pool Surabaya',
            'location' => 'Surabaya',
            'capacity' => 10
        ]);

        $userJakarta = User::factory()->create([
            'role' => 'pool',
            'pool_id' => $poolJakarta->id
        ]);

        $userSurabaya = User::factory()->create([
            'role' => 'pool',
            'pool_id' => $poolSurabaya->id
        ]);

        $vehicleJkt = Vehicle::factory()->create([
            'pool_id' => $poolJakarta->id,
            'status' => 'available',
            'brand' => 'goldenbird'
        ]);

        $vehicleSby = Vehicle::factory()->create([
            'pool_id' => $poolSurabaya->id,
            'status' => 'available',
            'brand' => 'goldenbird'
        ]);

        $driverJkt = Driver::factory()->create([
            'pool_id' => $poolJakarta->id,
            'status' => 'available'
        ]);

        $driverSby = Driver::factory()->create([
            'pool_id' => $poolSurabaya->id,
            'status' => 'available'
        ]);

        $category = ProductCategory::firstOrCreate([
            'name' => 'Long Term',
            'type' => 'long_term'
        ]);

        $product = Product::firstOrCreate([
            'kpi_key' => 'mobil_long'
        ], [
            'product_category_id' => $category->id,
            'name' => 'Mobil Long Term',
            'sku' => 'PRD-MOBIL-LT',
            'base_price' => 25000000,
            'unit' => 'trip',
            'is_active' => true
        ]);

        $categoryService = ProductCategory::firstOrCreate([
            'name' => 'Service',
            'type' => 'service'
        ]);

        $productDriver = Product::firstOrCreate([
            'kpi_key' => 'supir'
        ], [
            'product_category_id' => $categoryService->id,
            'name' => 'Supir',
            'sku' => 'PRD-SUPIR',
            'base_price' => 300000,
            'unit' => 'trip',
            'is_active' => true
        ]);

        return compact(
            'poolJakarta', 'poolSurabaya',
            'userJakarta', 'userSurabaya',
            'vehicleJkt', 'vehicleSby',
            'driverJkt', 'driverSby',
            'product', 'productDriver'
        );
    }

    /** @test */
    public function pool_user_only_sees_vehicles_from_their_own_pool(): void
    {
        $data = $this->setupDbData();

        // Acting as Jakarta user, we should only see Jakarta vehicle
        $response = $this->actingAs($data['userJakarta'])
            ->get(route('fleet.index'));
        
        $response->assertOk();
        $response->assertSee($data['vehicleJkt']->plate_number);
        $response->assertDontSee($data['vehicleSby']->plate_number);

        // Acting as Surabaya user, we should only see Surabaya vehicle
        $response = $this->actingAs($data['userSurabaya'])
            ->get(route('fleet.index'));

        $response->assertOk();
        $response->assertSee($data['vehicleSby']->plate_number);
        $response->assertDontSee($data['vehicleJkt']->plate_number);
    }

    /** @test */
    public function pool_user_cannot_assign_vehicles_or_drivers_from_another_pool(): void
    {
        $data = $this->setupDbData();

        $opp = Opportunity::factory()->create([
            'stage' => 'won',
            'products' => [
                [
                    'category' => 'Mobil Long Term',
                    'quantity' => 1,
                    'estimatedValue' => 25000000
                ]
            ]
        ]);

        // Jakarta user tries to assign Surabaya vehicle - should return 403
        $response = $this->actingAs($data['userJakarta'])
            ->postJson("/api/vehicles/assign-to-opportunity/{$opp->id}", [
                'vehicle_ids' => [$data['vehicleSby']->id]
            ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function won_opportunity_mobil_long_term_without_assigned_vehicle_appears_in_pending(): void
    {
        $data = $this->setupDbData();

        $opp = Opportunity::factory()->create([
            'stage' => 'won',
            'products' => [
                [
                    'category' => 'Mobil Long Term',
                    'quantity' => 1,
                    'estimatedValue' => 25000000
                ]
            ]
        ]);

        $response = $this->actingAs($data['userJakarta'])
            ->get(route('fleet.index'));

        $response->assertOk();
        $response->assertSee($opp->title);
    }

    /** @test */
    public function assigning_vehicle_to_won_opportunity_updates_vehicle_status_and_id(): void
    {
        $data = $this->setupDbData();

        $opp = Opportunity::factory()->create([
            'stage' => 'won',
            'products' => [
                [
                    'category' => 'Mobil Long Term',
                    'quantity' => 1,
                    'estimatedValue' => 25000000
                ]
            ]
        ]);

        $response = $this->actingAs($data['userJakarta'])
            ->postJson("/api/vehicles/assign-to-opportunity/{$opp->id}", [
                'vehicle_ids' => [$data['vehicleJkt']->id]
            ]);

        $response->assertOk();
        
        $vehicle = $data['vehicleJkt']->fresh();
        $this->assertEquals('assigned', $vehicle->status);
        $this->assertEquals($opp->id, $vehicle->assigned_opportunity_id);
    }

    /** @test */
    public function deselected_vehicle_returns_to_available_and_id_becomes_null(): void
    {
        $data = $this->setupDbData();

        $opp = Opportunity::factory()->create([
            'stage' => 'won',
            'products' => [
                [
                    'category' => 'Mobil Long Term',
                    'quantity' => 1,
                    'estimatedValue' => 25000000
                ]
            ]
        ]);

        // Pre-assign
        $data['vehicleJkt']->update([
            'assigned_opportunity_id' => $opp->id,
            'status' => 'assigned'
        ]);

        // Post empty arrays (deselect/release)
        $response = $this->actingAs($data['userJakarta'])
            ->postJson("/api/vehicles/assign-to-opportunity/{$opp->id}", [
                'vehicle_ids' => []
            ]);

        $response->assertOk();

        $vehicle = $data['vehicleJkt']->fresh();
        $this->assertEquals('available', $vehicle->status);
        $this->assertNull($vehicle->assigned_opportunity_id);
    }

    /** @test */
    public function driver_assignment_behaves_the_same_as_vehicle(): void
    {
        $data = $this->setupDbData();

        $opp = Opportunity::factory()->create([
            'stage' => 'won',
            'products' => [
                [
                    'category' => 'Supir',
                    'quantity' => 1,
                    'estimatedValue' => 300000
                ]
            ]
        ]);

        $response = $this->actingAs($data['userJakarta'])
            ->postJson("/api/vehicles/assign-to-opportunity/{$opp->id}", [
                'driver_ids' => [$data['driverJkt']->id]
            ]);

        $response->assertOk();

        $driver = $data['driverJkt']->fresh();
        $this->assertEquals('assigned', $driver->status);
        $this->assertEquals($opp->id, $driver->assigned_opportunity_id);
    }

    /** @test */
    public function selected_count_cannot_exceed_required_qty(): void
    {
        $data = $this->setupDbData();

        $opp = Opportunity::factory()->create([
            'stage' => 'won',
            'products' => [
                [
                    'category' => 'Mobil Long Term',
                    'quantity' => 1,
                    'estimatedValue' => 25000000
                ]
            ]
        ]);

        $secondVehicleJkt = Vehicle::factory()->create([
            'pool_id' => $data['poolJakarta']->id,
            'status' => 'available',
            'brand' => 'goldenbird'
        ]);

        // Try to assign 2 vehicles when required qty is only 1
        $response = $this->actingAs($data['userJakarta'])
            ->postJson("/api/vehicles/assign-to-opportunity/{$opp->id}", [
                'vehicle_ids' => [$data['vehicleJkt']->id, $secondVehicleJkt->id]
            ]);

        $response->assertStatus(422);
        $response->assertJsonFragment(['message' => 'Jumlah kendaraan melebihi kebutuhan (1 unit).']);
    }
}
