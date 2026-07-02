<?php

namespace Tests\Feature;

use App\Models\Outlet;
use App\Models\OutletStock;
use App\Models\Product;
use App\Models\Dispatch;
use App\Models\DispatchItem;
use App\Models\SalesLog;
use App\Models\SalesLogItem;
use App\Models\Material;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OutletPortalTest extends TestCase
{
    use RefreshDatabase;

    protected $shouldAuthenticate = false;

    protected $ownOutlet;
    protected $franchiseOutlet;
    protected $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->ownOutlet = Outlet::create([
            'name' => 'Own Outlet Beach',
            'type' => 'own',
            'commission_rate' => 0.00,
            'email' => 'beach@dessertops.com',
            'password' => bcrypt('password'),
        ]);

        $this->franchiseOutlet = Outlet::create([
            'name' => 'Franchise Partner Thrissur',
            'type' => 'franchise',
            'commission_rate' => 15.00,
            'email' => 'thrissur@dessertops.com',
            'password' => bcrypt('password'),
        ]);

        $this->product = Product::create([
            'name' => 'Chocolate Lava Cake',
            'sku' => 'DSR-CLC-01',
            'retail_price' => 150.00,
            'current_kitchen_stock' => 100.00,
        ]);
    }

    public function test_accessing_portal_dashboard_is_blocked_without_selecting_outlet()
    {
        $response = $this->get(route('portal.dashboard'));
        $response->assertRedirect(route('portal.login'));
    }

    public function test_can_select_outlet_and_access_portal_dashboard()
    {
        // Select own outlet
        $response = $this->post(route('portal.login.post'), [
            'email' => 'beach@dessertops.com',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('portal.dashboard'));
        $response->assertSessionHas('portal_outlet_id', $this->ownOutlet->id);

        // Can access dashboard now
        $dashboardResponse = $this->actingAs($this->ownOutlet, 'outlet')
            ->get(route('portal.dashboard'));
        
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertSee($this->ownOutlet->name);
    }

    public function test_can_logout_outlet_portal()
    {
        $response = $this->actingAs($this->ownOutlet, 'outlet')
            ->post(route('portal.logout'));

        $response->assertRedirect(route('portal.login'));
        $this->assertFalse(session()->has('portal_outlet_id'));
    }

    public function test_can_receive_dispatch_via_portal_updates_outlet_stock()
    {
        $dispatch = Dispatch::create([
            'dispatch_number' => 'DISP-2026-9999',
            'outlet_id' => $this->ownOutlet->id,
            'dispatch_date' => now(),
            'status' => 'dispatched',
        ]);

        DispatchItem::create([
            'dispatch_id' => $dispatch->id,
            'product_id' => $this->product->id,
            'quantity' => 25.00,
        ]);

        // Attempting to receive without session is blocked
        $response = $this->post(route('portal.dispatches.receive', $dispatch->id));
        $response->assertRedirect(route('portal.login'));

        // Receive with active session
        $response = $this->actingAs($this->ownOutlet, 'outlet')
            ->post(route('portal.dispatches.receive', $dispatch->id));

        $response->assertRedirect(route('portal.dispatches'));
        $dispatch->refresh();
        $this->assertEquals('received', $dispatch->status);

        // Check local stock updated
        $outletStock = OutletStock::where('outlet_id', $this->ownOutlet->id)
            ->where('product_id', $this->product->id)
            ->first();
        
        $this->assertNotNull($outletStock);
        $this->assertEquals(25.00, $outletStock->quantity);
    }

    public function test_cannot_receive_other_outlets_dispatch()
    {
        $dispatch = Dispatch::create([
            'dispatch_number' => 'DISP-2026-8888',
            'outlet_id' => $this->franchiseOutlet->id, // destined for franchise outlet
            'dispatch_date' => now(),
            'status' => 'dispatched',
        ]);

        DispatchItem::create([
            'dispatch_id' => $dispatch->id,
            'product_id' => $this->product->id,
            'quantity' => 20.00,
        ]);

        // Access as ownOutlet
        $response = $this->actingAs($this->ownOutlet, 'outlet')
            ->post(route('portal.dispatches.receive', $dispatch->id));

        $response->assertSessionHas('error', 'Unauthorized action for this outlet.');
        $dispatch->refresh();
        $this->assertEquals('dispatched', $dispatch->status); // unchanged
    }

    public function test_can_log_sales_via_portal_for_own_outlet()
    {
        // Seed stock first
        OutletStock::create([
            'outlet_id' => $this->ownOutlet->id,
            'product_id' => $this->product->id,
            'quantity' => 40.00,
        ]);

        $response = $this->actingAs($this->ownOutlet, 'outlet')
            ->post(route('portal.sales.store'), [
                'log_date' => now()->format('Y-m-d'),
                'items' => [
                    [
                        'product_id' => $this->product->id,
                        'quantity_sold' => 15.00,
                    ]
                ]
            ]);

        $response->assertRedirect(route('portal.sales.index'));
        
        // Verify stock decremented: 40 - 15 = 25
        $stock = OutletStock::where('outlet_id', $this->ownOutlet->id)
            ->where('product_id', $this->product->id)
            ->first();
        $this->assertEquals(25.00, $stock->quantity);

        // Verify financial calculations: 15 sold * ₹150 retail = ₹2250 total. Own outlet has 0% commission.
        $log = SalesLog::first();
        $this->assertNotNull($log);
        $this->assertDatabaseHas('sales_log_items', [
            'sales_log_id' => $log->id,
            'product_id' => $this->product->id,
            'quantity_sold' => 15.00,
            'unit_price' => 150.00,
            'total_revenue' => 2250.00,
            'commission_amount' => 0.00,
            'net_revenue' => 2250.00,
        ]);
    }

    public function test_can_log_sales_via_portal_for_franchise_outlet()
    {
        // Seed stock first
        OutletStock::create([
            'outlet_id' => $this->franchiseOutlet->id,
            'product_id' => $this->product->id,
            'quantity' => 30.00,
        ]);

        $response = $this->actingAs($this->franchiseOutlet, 'outlet')
            ->post(route('portal.sales.store'), [
                'log_date' => now()->format('Y-m-d'),
                'items' => [
                    [
                        'product_id' => $this->product->id,
                        'quantity_sold' => 10.00,
                    ]
                ]
            ]);

        $response->assertRedirect(route('portal.sales.index'));
        
        // Verify stock decremented: 30 - 10 = 20
        $stock = OutletStock::where('outlet_id', $this->franchiseOutlet->id)
            ->where('product_id', $this->product->id)
            ->first();
        $this->assertEquals(20.00, $stock->quantity);

        // Verify financial calculations: 10 sold * ₹150 retail = ₹1500 total. Franchise has 15% commission = ₹225. Net = ₹1275.
        $log = SalesLog::first();
        $this->assertNotNull($log);
        $this->assertDatabaseHas('sales_log_items', [
            'sales_log_id' => $log->id,
            'product_id' => $this->product->id,
            'quantity_sold' => 10.00,
            'unit_price' => 150.00,
            'total_revenue' => 1500.00,
            'commission_amount' => 225.00,
            'net_revenue' => 1275.00,
        ]);
    }

    public function test_cannot_log_sales_via_portal_if_insufficient_stock()
    {
        // Seed stock first
        OutletStock::create([
            'outlet_id' => $this->ownOutlet->id,
            'product_id' => $this->product->id,
            'quantity' => 5.00,
        ]);

        $response = $this->actingAs($this->ownOutlet, 'outlet')
            ->post(route('portal.sales.store'), [
                'log_date' => now()->format('Y-m-d'),
                'items' => [
                    [
                        'product_id' => $this->product->id,
                        'quantity_sold' => 10.00, // exceeds available stock of 5
                    ]
                ]
            ]);

        $response->assertSessionHas('error');
        $this->assertNull(SalesLog::first());

        // Stock should remain 5
        $stock = OutletStock::where('outlet_id', $this->ownOutlet->id)
            ->where('product_id', $this->product->id)
            ->first();
        $this->assertEquals(5.00, $stock->quantity);
    }

    public function test_outlet_can_request_products_triggering_notification()
    {
        \Illuminate\Support\Facades\Notification::fake();

        $admin = \App\Models\User::factory()->create([
            'email' => 'admin@dessertops.com',
        ]);

        $response = $this->actingAs($this->ownOutlet, 'outlet')
            ->post(route('portal.requests.store'), [
                'notes' => 'Urgent stock refill requested.',
                'items' => [
                    [
                        'item_id' => "product:{$this->product->id}",
                        'quantity' => 50,
                    ]
                ]
            ]);

        $response->assertRedirect(route('portal.dispatches'));
        
        $dispatch = Dispatch::first();
        $this->assertNotNull($dispatch);
        $this->assertEquals('pending', $dispatch->status);
        $this->assertEquals($this->ownOutlet->id, $dispatch->outlet_id);
        $this->assertDatabaseHas('dispatch_items', [
            'dispatch_id' => $dispatch->id,
            'product_id' => $this->product->id,
            'quantity' => 50,
        ]);

        \Illuminate\Support\Facades\Notification::assertSentTo(
            $admin,
            \App\Notifications\ProductRequestReceived::class
        );
    }

    public function test_dispatching_shipment_sends_notification_to_outlet()
    {
        \Illuminate\Support\Facades\Notification::fake();

        $dispatch = Dispatch::create([
            'dispatch_number' => 'DISP-2026-TEST1',
            'outlet_id' => $this->ownOutlet->id,
            'dispatch_date' => now(),
            'status' => 'pending',
        ]);

        $admin = \App\Models\User::factory()->create();

        $response = $this->actingAs($admin)
            ->post(route('dispatches.dispatch', $dispatch->id));

        $response->assertRedirect();
        \Illuminate\Support\Facades\Notification::assertSentTo(
            $this->ownOutlet,
            \App\Notifications\ShipmentDispatched::class
        );
    }

    public function test_receiving_shipment_sends_notification_to_admin()
    {
        \Illuminate\Support\Facades\Notification::fake();

        $dispatch = Dispatch::create([
            'dispatch_number' => 'DISP-2026-TEST2',
            'outlet_id' => $this->ownOutlet->id,
            'dispatch_date' => now(),
            'status' => 'dispatched',
        ]);

        $admin = \App\Models\User::factory()->create();

        $response = $this->actingAs($this->ownOutlet, 'outlet')
            ->post(route('portal.dispatches.receive', $dispatch->id));

        $response->assertRedirect();
        \Illuminate\Support\Facades\Notification::assertSentTo(
            $admin,
            \App\Notifications\ShipmentReceived::class
        );
    }

    public function test_can_request_packaging_materials_by_piece_and_dispatch_receives_them()
    {
        // 1. Create a packaging material with per_box_qty of 100
        $material = Material::create([
            'name' => 'Dessert Box Medium',
            'sku' => 'PKG-BOX-MED',
            'category' => 'packaging',
            'unit' => 'box',
            'current_stock' => 10.00,
            'kitchen_stock' => 5.00, // 5 boxes = 500 pieces
            'min_stock_alert' => 2.00,
            'per_box_qty' => 100,
        ]);

        // 2. Outlet requests 150 pieces of this packaging material
        $response = $this->actingAs($this->ownOutlet, 'outlet')
            ->post(route('portal.requests.store'), [
                'notes' => 'Need boxes.',
                'items' => [
                    [
                        'item_id' => "material:{$material->id}",
                        'quantity' => 150, // 150 pieces
                    ]
                ]
            ]);

        $response->assertRedirect(route('portal.dispatches'));

        // Verify dispatch item created
        $dispatch = Dispatch::first();
        $this->assertNotNull($dispatch);
        $this->assertEquals('pending', $dispatch->status);

        $dispatchItem = DispatchItem::first();
        $this->assertNotNull($dispatchItem);
        $this->assertEquals($material->id, $dispatchItem->material_id);
        $this->assertNull($dispatchItem->product_id);
        $this->assertEquals(150.00, $dispatchItem->quantity);

        // 3. Admin dispatches the shipment (decrements kitchen stock by 1.50 boxes)
        $admin = \App\Models\User::factory()->create();
        $dispatchResponse = $this->actingAs($admin)
            ->post(route('dispatches.dispatch', $dispatch->id));

        $dispatchResponse->assertRedirect();
        $dispatch->refresh();
        $this->assertEquals('dispatched', $dispatch->status);

        // Kitchen stock should decrease from 5.0 to 3.5 boxes
        $material->refresh();
        $this->assertEquals(3.50, $material->kitchen_stock);

        // 4. Outlet receives the shipment (increments outlet stock by 150 pieces)
        $receiveResponse = $this->actingAs($this->ownOutlet, 'outlet')
            ->post(route('portal.dispatches.receive', $dispatch->id));

        $receiveResponse->assertRedirect(route('portal.dispatches'));
        $dispatch->refresh();
        $this->assertEquals('received', $dispatch->status);

        // OutletStock should be created with 150 pieces
        $outletStock = OutletStock::where('outlet_id', $this->ownOutlet->id)
            ->where('material_id', $material->id)
            ->first();

        $this->assertNotNull($outletStock);
        $this->assertEquals(150.00, $outletStock->quantity);
    }
}
