<?php

namespace Tests\Feature;

use App\Models\Outlet;
use App\Models\OutletStock;
use App\Models\Product;
use App\Models\ShowcaseRequest;
use App\Models\ShowcaseRequestItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortalShowcaseRequestTest extends TestCase
{
    use RefreshDatabase;

    protected $outlet;
    protected $product;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->outlet = Outlet::create([
            'name' => 'Outlet Beach',
            'type' => 'own',
            'commission_rate' => 0.00,
            'email' => 'beach@dessertops.com',
            'password' => bcrypt('password'),
        ]);

        $this->product = Product::create([
            'name' => 'Chocolate Lava Cake',
            'sku' => 'DSR-CLC-01',
            'retail_price' => 150.00,
            'current_kitchen_stock' => 100.00,
        ]);

        // Assign product to outlet
        $this->outlet->assignedProducts()->attach($this->product->id);

        // Initialize outlet stock
        OutletStock::create([
            'outlet_id' => $this->outlet->id,
            'product_id' => $this->product->id,
            'quantity' => 50.00,
            'store_quantity' => 30.00,
            'kitchen_quantity' => 20.00,
            'showcase_quantity' => 0.00,
        ]);

        // Admin User for approval/release check
        $this->user = User::create([
            'name' => 'Manager John',
            'email' => 'manager@dessertops.com',
            'password' => bcrypt('password'),
            'role' => 'store_manager',
        ]);
    }

    public function test_can_list_showcase_requests()
    {
        $request = ShowcaseRequest::create([
            'request_number' => 'SR-2026-0001',
            'outlet_id' => $this->outlet->id,
            'requested_by' => 'Staff Alice',
            'requested_date' => now()->toDateString(),
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->outlet, 'outlet')
            ->withSession(['portal_outlet_id' => $this->outlet->id])
            ->get(route('portal.showcase-requests.index'));

        $response->assertStatus(200);
        $response->assertSee('SR-2026-0001');
    }

    public function test_can_access_create_showcase_request_form()
    {
        $response = $this->actingAs($this->outlet, 'outlet')
            ->withSession(['portal_outlet_id' => $this->outlet->id])
            ->get(route('portal.showcase-requests.create'));

        $response->assertStatus(200);
        $response->assertSee('Chocolate Lava Cake');
    }

    public function test_can_store_showcase_request()
    {
        $response = $this->actingAs($this->outlet, 'outlet')
            ->withSession(['portal_outlet_id' => $this->outlet->id])
            ->post(route('portal.showcase-requests.store'), [
                'requested_by' => 'Alice Manager',
                'notes' => 'Replenish showcase for evening rush',
                'items' => [
                    [
                        'product_id' => $this->product->id,
                        'quantity_requested' => 10.00,
                    ]
                ]
            ]);

        $response->assertRedirect(route('portal.showcase-requests.index'));
        $this->assertDatabaseHas('showcase_requests', [
            'requested_by' => 'Alice Manager',
            'notes' => 'Replenish showcase for evening rush',
        ]);

        $request = ShowcaseRequest::first();
        $this->assertNotNull($request);
        $this->assertStringStartsWith('SR-', $request->request_number);

        $this->assertDatabaseHas('showcase_request_items', [
            'showcase_request_id' => $request->id,
            'product_id' => $this->product->id,
            'quantity_requested' => 10.00,
        ]);
    }

    public function test_can_approve_showcase_request()
    {
        $request = ShowcaseRequest::create([
            'request_number' => 'SR-2026-0002',
            'outlet_id' => $this->outlet->id,
            'requested_by' => 'Staff Alice',
            'requested_date' => now()->toDateString(),
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->outlet, 'outlet')
            ->withSession(['portal_outlet_id' => $this->outlet->id])
            ->post(route('portal.showcase-requests.approve', $request->id));

        $response->assertRedirect(route('portal.showcase-requests.show', $request->id));
        $this->assertEquals('approved', $request->fresh()->status);
    }

    public function test_can_reject_showcase_request()
    {
        $request = ShowcaseRequest::create([
            'request_number' => 'SR-2026-0003',
            'outlet_id' => $this->outlet->id,
            'requested_by' => 'Staff Alice',
            'requested_date' => now()->toDateString(),
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->outlet, 'outlet')
            ->withSession(['portal_outlet_id' => $this->outlet->id])
            ->post(route('portal.showcase-requests.reject', $request->id));

        $response->assertRedirect(route('portal.showcase-requests.show', $request->id));
        $this->assertEquals('rejected', $request->fresh()->status);
    }

    public function test_can_release_showcase_request()
    {
        $request = ShowcaseRequest::create([
            'request_number' => 'SR-2026-0004',
            'outlet_id' => $this->outlet->id,
            'requested_by' => 'Staff Alice',
            'requested_date' => now()->toDateString(),
            'status' => 'approved',
        ]);

        $item = ShowcaseRequestItem::create([
            'showcase_request_id' => $request->id,
            'product_id' => $this->product->id,
            'quantity_requested' => 10.00,
        ]);

        // Release 10 units from store stock
        $response = $this->actingAs($this->outlet, 'outlet')
            ->withSession(['portal_outlet_id' => $this->outlet->id])
            ->actingAs($this->user, 'web') // Logged in as manager on web guard
            ->post(route('portal.showcase-requests.release', $request->id), [
                'items' => [
                    $item->id => [
                        'release_source' => 'store',
                        'quantity_released' => 10.00,
                    ]
                ]
            ]);

        $response->assertRedirect(route('portal.showcase-requests.show', $request->id));
        $this->assertEquals('released', $request->fresh()->status);

        $item->refresh();
        $this->assertEquals(10.00, $item->quantity_released);
        $this->assertEquals('Store', $item->release_source);

        // Verify stock adjustments:
        // Decremented store_quantity: 30.00 - 10.00 = 20.00
        // Incremented showcase_quantity: 0.00 + 10.00 = 10.00
        // Incremented total quantity: 50.00 + 10.00 = 60.00 (as per the prompt's instruction to increment total quantity)
        $stock = OutletStock::where('outlet_id', $this->outlet->id)
            ->where('product_id', $this->product->id)
            ->first();

        $this->assertEquals(20.00, $stock->store_quantity);
        $this->assertEquals(10.00, $stock->showcase_quantity);
        $this->assertEquals(60.00, $stock->quantity);

        // Verify stock movement logged
        $this->assertDatabaseHas('outlet_stock_movements', [
            'outlet_id' => $this->outlet->id,
            'product_id' => $this->product->id,
            'from_location' => 'store',
            'to_location' => 'showcase',
            'quantity' => 10.00,
            'reference' => 'SR-2026-0004',
        ]);
    }
}
