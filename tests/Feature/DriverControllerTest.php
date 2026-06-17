<?php

namespace Tests\Feature;

use App\Models\Driver;
use App\Models\Pool;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DriverControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function createUser(string $role, ?int $poolId = null): User
    {
        return User::factory()->create([
            'role' => $role,
            'pool_id' => $poolId,
        ]);
    }

    // --- Index Method Tests ---

    #[Test]
    public function index_blocks_finance_role(): void
    {
        $finance = $this->createUser('finance');

        $response = $this->actingAs($finance)->get(route('drivers.index'));

        $response->assertStatus(403);
    }

    #[Test]
    public function index_shows_all_drivers_for_manager_or_gm(): void
    {
        $gm = $this->createUser('gm');
        
        $pool1 = Pool::create(['name' => 'Pool A', 'location' => 'Loc A']);
        $pool2 = Pool::create(['name' => 'Pool B', 'location' => 'Loc B']);
        
        Driver::factory()->create(['pool_id' => $pool1->id]);
        Driver::factory()->create(['pool_id' => $pool2->id]);

        $response = $this->actingAs($gm)->get(route('drivers.index'));

        $response->assertStatus(200);
        $response->assertViewHas('drivers');
        $this->assertCount(2, $response->viewData('drivers'));
    }

    #[Test]
    public function index_restricts_drivers_by_pool_for_operational_and_pool_roles(): void
    {
        $pool1 = Pool::create(['name' => 'Pool A', 'location' => 'Loc A']);
        $pool2 = Pool::create(['name' => 'Pool B', 'location' => 'Loc B']);
        
        $poolUser = $this->createUser('pool', $pool1->id);

        Driver::factory()->create(['pool_id' => $pool1->id, 'name' => 'Driver Pool A']);
        Driver::factory()->create(['pool_id' => $pool2->id, 'name' => 'Driver Pool B']);

        $response = $this->actingAs($poolUser)->get(route('drivers.index'));

        $response->assertStatus(200);
        $response->assertViewHas('drivers');
        $drivers = $response->viewData('drivers');
        
        $this->assertCount(1, $drivers);
        $this->assertEquals('Driver Pool A', $drivers->first()->name);
    }

    #[Test]
    public function index_filters_drivers_by_search_query(): void
    {
        $gm = $this->createUser('gm');
        
        Driver::factory()->create(['name' => 'John Doe', 'phone' => '111111']);
        Driver::factory()->create(['name' => 'Jane Smith', 'phone' => '222222']);

        $response = $this->actingAs($gm)->get(route('drivers.index', ['search' => 'John']));

        $response->assertStatus(200);
        $drivers = $response->viewData('drivers');
        $this->assertCount(1, $drivers);
        $this->assertEquals('John Doe', $drivers->first()->name);
    }

    #[Test]
    public function index_filters_drivers_by_status(): void
    {
        $gm = $this->createUser('gm');
        
        Driver::factory()->create(['name' => 'Available Driver', 'status' => 'available']);
        Driver::factory()->create(['name' => 'Inactive Driver', 'status' => 'inactive']);

        $response = $this->actingAs($gm)->get(route('drivers.index', ['status' => 'available']));

        $response->assertStatus(200);
        $drivers = $response->viewData('drivers');
        $this->assertCount(1, $drivers);
        $this->assertEquals('Available Driver', $drivers->first()->name);
    }

    // --- Show Method Tests ---

    #[Test]
    public function show_blocks_finance_role(): void
    {
        $finance = $this->createUser('finance');
        $driver = Driver::factory()->create();

        $response = $this->actingAs($finance)->get(route('drivers.show', $driver));

        $response->assertStatus(403);
    }

    #[Test]
    public function show_displays_driver_details_for_authorized_roles(): void
    {
        $gm = $this->createUser('gm');
        $pool = Pool::create(['name' => 'Pool A', 'location' => 'Loc A']);
        $driver = Driver::factory()->create(['pool_id' => $pool->id]);

        $response = $this->actingAs($gm)->get(route('drivers.show', $driver));

        $response->assertStatus(200);
        $response->assertViewHas('driver');
        $this->assertEquals($driver->id, $response->viewData('driver')->id);
    }

    #[Test]
    public function show_restricts_driver_view_by_pool_for_pool_role(): void
    {
        $pool1 = Pool::create(['name' => 'Pool A', 'location' => 'Loc A']);
        $pool2 = Pool::create(['name' => 'Pool B', 'location' => 'Loc B']);
        
        $poolUser = $this->createUser('pool', $pool1->id);

        $driverFromOtherPool = Driver::factory()->create(['pool_id' => $pool2->id]);

        $response = $this->actingAs($poolUser)->get(route('drivers.show', $driverFromOtherPool));

        // Assuming Laravel implicit binding respects the Global Scope we discovered.
        // It should result in a 404 ModelNotFoundException if the global scope is correctly applied.
        $response->assertStatus(404);
    }

    // --- Store Method Tests ---

    #[Test]
    public function store_blocks_unauthorized_roles(): void
    {
        $sales = $this->createUser('sales');
        $finance = $this->createUser('finance');

        $payload = [
            'name' => 'New Driver',
            'phone' => '1234567890',
            'status' => 'available',
        ];

        $responseSales = $this->actingAs($sales)->post(route('drivers.store'), $payload);
        $responseSales->assertStatus(403);

        $responseFinance = $this->actingAs($finance)->post(route('drivers.store'), $payload);
        $responseFinance->assertStatus(403);
    }

    #[Test]
    public function store_allows_authorized_roles_and_creates_driver(): void
    {
        $manager = $this->createUser('manager');
        $pool = Pool::create(['name' => 'Pool A', 'location' => 'Loc A']);

        $payload = [
            'name' => 'New Manager Driver',
            'phone' => '1234567890',
            'pool_id' => $pool->id,
            'status' => 'available',
        ];

        $response = $this->actingAs($manager)->post(route('drivers.store'), $payload);

        $response->assertRedirect(route('drivers.index'));
        $response->assertSessionHas('success', 'Driver registered successfully.');
        
        $this->assertDatabaseHas('drivers', [
            'name' => 'New Manager Driver',
            'phone' => '1234567890',
            'pool_id' => $pool->id,
            'status' => 'available',
        ]);
    }

    #[Test]
    public function store_validates_required_fields(): void
    {
        $gm = $this->createUser('gm');

        $response = $this->actingAs($gm)->post(route('drivers.store'), []);

        $response->assertSessionHasErrors(['name', 'phone', 'status']);
    }

    #[Test]
    public function store_forces_pool_id_for_pool_users_and_fails_if_none(): void
    {
        // Pool user without a pool_id assigned should get 403
        $poolUserNoPool = $this->createUser('pool', null);
        
        $payload = [
            'name' => 'Pool Driver',
            'phone' => '999999999',
            'status' => 'available',
        ];

        $response1 = $this->actingAs($poolUserNoPool)->post(route('drivers.store'), $payload);
        $response1->assertStatus(403);
        $this->assertEquals('Pengguna pool wajib memiliki pool_id.', $response1->exception->getMessage());

        // Pool user with a pool_id should succeed and override any provided pool_id
        $pool = Pool::create(['name' => 'Pool User Pool', 'location' => 'Loc A']);
        $poolUser = $this->createUser('pool', $pool->id);
        
        $otherPool = Pool::create(['name' => 'Other Pool', 'location' => 'Loc B']);
        
        // Attempt to pass another pool_id, it should be overridden
        $payload['pool_id'] = $otherPool->id;

        $response2 = $this->actingAs($poolUser)->post(route('drivers.store'), $payload);
        $response2->assertRedirect(route('drivers.index'));

        $this->assertDatabaseHas('drivers', [
            'name' => 'Pool Driver',
            'pool_id' => $pool->id, // Important: should be the user's pool, not the one in payload
        ]);
    }
}
