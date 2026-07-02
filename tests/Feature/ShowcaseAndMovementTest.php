<?php

namespace Tests\Feature;

use App\Models\Material;
use App\Models\Outlet;
use App\Models\OutletStockMovement;
use App\Models\Product;
use App\Models\ShowcaseRequest;
use App\Models\ShowcaseRequestItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShowcaseAndMovementTest extends TestCase
{
    use RefreshDatabase;

    public function test_showcase_request_model_relationships_and_casts()
    {
        $outlet = Outlet::factory()->create();
        $product = Product::factory()->create();

        // 1. Create ShowcaseRequest
        $request = ShowcaseRequest::create([
            'request_number' => 'SR-12345',
            'outlet_id' => $outlet->id,
            'requested_by' => 'John Doe',
            'requested_date' => '2026-07-02',
            'status' => 'pending',
            'notes' => 'Urgent request for display',
        ]);

        $this->assertDatabaseHas('showcase_requests', [
            'request_number' => 'SR-12345',
            'outlet_id' => $outlet->id,
            'requested_by' => 'John Doe',
            'status' => 'pending',
        ]);

        // Assert casts
        $this->assertInstanceOf(\Carbon\Carbon::class, $request->requested_date);
        $this->assertEquals('2026-07-02', $request->requested_date->format('Y-m-d'));

        // Assert relationships
        $this->assertTrue($request->outlet->is($outlet));
        $this->assertTrue($outlet->showcaseRequests->contains($request));

        // 2. Create ShowcaseRequestItem
        $item = ShowcaseRequestItem::create([
            'showcase_request_id' => $request->id,
            'product_id' => $product->id,
            'quantity_requested' => 15.50,
            'quantity_released' => 10.00,
            'release_source' => 'Main Kitchen',
        ]);

        $this->assertDatabaseHas('showcase_request_items', [
            'showcase_request_id' => $request->id,
            'product_id' => $product->id,
            'quantity_requested' => 15.50,
            'quantity_released' => 10.00,
            'release_source' => 'Main Kitchen',
        ]);

        // Assert casts
        $this->assertEquals(15.50, $item->quantity_requested);
        $this->assertEquals(10.00, $item->quantity_released);

        // Assert relationships
        $this->assertTrue($item->showcaseRequest->is($request));
        $this->assertTrue($item->product->is($product));
        $this->assertTrue($request->items->contains($item));
        $this->assertTrue($product->showcaseRequestItems->contains($item));
    }

    public function test_outlet_stock_movement_model_relationships_and_casts()
    {
        $outlet = Outlet::factory()->create();
        $product = Product::factory()->create();
        $material = Material::factory()->create();

        // 1. Movement for a Product
        $productMovement = OutletStockMovement::create([
            'outlet_id' => $outlet->id,
            'product_id' => $product->id,
            'material_id' => null,
            'from_location' => 'kitchen',
            'to_location' => 'showcase',
            'quantity' => 20.00,
            'logged_by' => 'Chef Smith',
            'reference' => 'SR-12345',
        ]);

        $this->assertDatabaseHas('outlet_stock_movements', [
            'outlet_id' => $outlet->id,
            'product_id' => $product->id,
            'material_id' => null,
            'from_location' => 'kitchen',
            'to_location' => 'showcase',
            'quantity' => 20.00,
            'logged_by' => 'Chef Smith',
            'reference' => 'SR-12345',
        ]);

        // Assert relationships and casts
        $this->assertTrue($productMovement->outlet->is($outlet));
        $this->assertTrue($productMovement->product->is($product));
        $this->assertNull($productMovement->material);
        $this->assertEquals(20.00, $productMovement->quantity);

        $this->assertTrue($outlet->stockMovements->contains($productMovement));
        $this->assertTrue($product->outletStockMovements->contains($productMovement));

        // 2. Movement for a Material
        $materialMovement = OutletStockMovement::create([
            'outlet_id' => $outlet->id,
            'product_id' => null,
            'material_id' => $material->id,
            'from_location' => 'store',
            'to_location' => 'kitchen',
            'quantity' => 5.25,
            'logged_by' => 'Helper Jane',
            'reference' => null,
        ]);

        $this->assertDatabaseHas('outlet_stock_movements', [
            'outlet_id' => $outlet->id,
            'product_id' => null,
            'material_id' => $material->id,
            'from_location' => 'store',
            'to_location' => 'kitchen',
            'quantity' => 5.25,
            'logged_by' => 'Helper Jane',
            'reference' => null,
        ]);

        // Assert relationships and casts
        $outlet->refresh();
        $this->assertTrue($materialMovement->outlet->is($outlet));
        $this->assertNull($materialMovement->product);
        $this->assertTrue($materialMovement->material->is($material));
        $this->assertEquals(5.25, $materialMovement->quantity);

        $this->assertTrue($outlet->stockMovements->contains($materialMovement));
        $this->assertTrue($material->outletStockMovements->contains($materialMovement));
    }
}
