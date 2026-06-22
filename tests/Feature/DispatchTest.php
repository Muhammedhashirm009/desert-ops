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

    public function test_can_receive_shipment_increments_outlet_stock()
    {
        $dispatch = Dispatch::create([
            'dispatch_number' => 'DISP-2026-0003',
            'outlet_id' => $this->outlet->id,
            'dispatch_date' => now(),
            'status' => 'dispatched',
        ]);

        DispatchItem::create([
            'dispatch_id' => $dispatch->id,
            'product_id' => $this->product->id,
            'quantity' => 20.00,
        ]);

        $response = $this->post(route('dispatches.receive', $dispatch->id));

        $response->assertRedirect(route('dispatches.show', $dispatch->id));
        $dispatch->refresh();
        $this->assertEquals('received', $dispatch->status);

        // Verify outlet stock incremented from 0 to 20
        $outletStock = OutletStock::where('outlet_id', $this->outlet->id)
            ->where('product_id', $this->product->id)
            ->first();
            
        $this->assertNotNull($outletStock);
        $this->assertEquals(20.00, $outletStock->quantity);
    }
}
