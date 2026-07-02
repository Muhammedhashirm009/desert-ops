<?php

namespace Tests\Feature;

use App\Models\Dispatch;
use App\Models\DispatchItem;
use App\Models\Outlet;
use App\Models\OutletStock;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DispatchTest extends TestCase
{
    use RefreshDatabase;

    protected $outlet;
    protected $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->outlet = Outlet::create([
            'name' => 'MG Road Outlet',
            'type' => 'own',
            'commission_rate' => 0.00,
        ]);

        $this->product = Product::create([
            'name' => 'Gulab Jamun Box',
            'sku' => 'DSR-GJB-01',
            'retail_price' => 250.00,
            'current_kitchen_stock' => 50.00,
        ]);
    }

    public function test_can_create_pending_dispatch()
    {
        $response = $this->post(route('dispatches.store'), [
            'outlet_id' => $this->outlet->id,
            'dispatch_date' => now()->format('Y-m-d'),
            'notes' => 'Weekly restock.',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 20.00,
                ]
            ]
        ]);

        $dispatch = Dispatch::first();
        $this->assertNotNull($dispatch);
        $response->assertRedirect(route('dispatches.show', $dispatch->id));

        $this->assertDatabaseHas('dispatches', [
            'dispatch_number' => $dispatch->dispatch_number,
            'status' => 'pending',
            'outlet_id' => $this->outlet->id,
        ]);

        $this->assertDatabaseHas('dispatch_items', [
            'dispatch_id' => $dispatch->id,
            'product_id' => $this->product->id,
            'quantity' => 20.00,
        ]);
    }

    public function test_can_dispatch_shipment_decrements_kitchen_stock()
    {
        $dispatch = Dispatch::create([
            'dispatch_number' => 'DISP-2026-0001',
            'outlet_id' => $this->outlet->id,
            'dispatch_date' => now(),
            'status' => 'pending',
        ]);

        DispatchItem::create([
            'dispatch_id' => $dispatch->id,
            'product_id' => $this->product->id,
            'quantity' => 15.00,
        ]);

        $response = $this->post(route('dispatches.dispatch', $dispatch->id));

        $response->assertRedirect(route('dispatches.show', $dispatch->id));
        $dispatch->refresh();
        $this->assertEquals('dispatched', $dispatch->status);

        // Verify kitchen stock decremented from 50 to 35
        $this->product->refresh();
        $this->assertEquals(35.00, $this->product->current_kitchen_stock);
    }

    public function test_cannot_dispatch_if_insufficient_kitchen_stock()
    {
        $dispatch = Dispatch::create([
            'dispatch_number' => 'DISP-2026-0002',
            'outlet_id' => $this->outlet->id,
            'dispatch_date' => now(),
            'status' => 'pending',
        ]);

        DispatchItem::create([
            'dispatch_id' => $dispatch->id,
            'product_id' => $this->product->id,
            'quantity' => 60.00, // exceeds kitchen stock of 50
        ]);

        $response = $this->post(route('dispatches.dispatch', $dispatch->id));

        $response->assertSessionHas('error');
        $dispatch->refresh();
        $this->assertEquals('pending', $dispatch->status);

        // Verify kitchen stock remains 50
        $this->product->refresh();
        $this->assertEquals(50.00, $this->product->current_kitchen_stock);
    }

    public function test_can_list_outlet_orders()
    {
        $dispatch = Dispatch::create([
            'dispatch_number' => 'DISP-2026-9999',
            'outlet_id' => $this->outlet->id,
            'dispatch_date' => now(),
            'status' => 'pending',
        ]);

        $response = $this->get(route('dispatches.orders'));

        $response->assertStatus(200);
        $response->assertSee('Pending Store Orders');
        $response->assertSee('DISP-2026-9999');
        $response->assertSee('MG Road Outlet');
    }

    public function test_can_cancel_outlet_order_and_lists_cancelled_orders()
    {
        $dispatch = Dispatch::create([
            'dispatch_number' => 'DISP-2026-8888',
            'outlet_id' => $this->outlet->id,
            'dispatch_date' => now(),
            'status' => 'pending',
        ]);

        // Cancel order
        $response = $this->delete(route('dispatches.destroy', $dispatch->id));
        $response->assertRedirect();
        
        $dispatch->refresh();
        $this->assertEquals('cancelled', $dispatch->status);

        // Verify cancelled order is visible on /outlet-orders
        $ordersResponse = $this->get(route('dispatches.orders'));
        $ordersResponse->assertSee('Recently Cancelled Orders');
        $ordersResponse->assertSee('DISP-2026-8888');
    }

    public function test_outlet_orders_displays_total_requirements_summary()
    {
        $dispatch1 = Dispatch::create([
            'dispatch_number' => 'DISP-2026-7771',
            'outlet_id' => $this->outlet->id,
            'dispatch_date' => now(),
            'status' => 'pending',
        ]);
        DispatchItem::create([
            'dispatch_id' => $dispatch1->id,
            'product_id' => $this->product->id,
            'quantity' => 12.00,
        ]);

        $dispatch2 = Dispatch::create([
            'dispatch_number' => 'DISP-2026-7772',
            'outlet_id' => $this->outlet->id,
            'dispatch_date' => now(),
            'status' => 'pending',
        ]);
        DispatchItem::create([
            'dispatch_id' => $dispatch2->id,
            'product_id' => $this->product->id,
            'quantity' => 8.00,
        ]);

        // Total should be 20 Units of Gulab Jamun Box
        $response = $this->get(route('dispatches.orders'));
        $response->assertStatus(200);
        $response->assertSee('Fulfillment Requirements Summary');
        $response->assertSee('20 Units');
        $response->assertSee('Gulab Jamun Box');
    }
}

