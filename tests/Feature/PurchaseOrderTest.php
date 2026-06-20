<?php

namespace Tests\Feature;

use App\Models\Material;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseOrderTest extends TestCase
{
    use RefreshDatabase;

    protected $supplier;
    protected $material1;
    protected $material2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->supplier = Supplier::create([
            'name' => 'Test Supplier',
        ]);

        $this->material1 = Material::create([
            'name' => 'Sugar',
            'sku' => 'RAW-SUG-01',
            'category' => 'ingredient',
            'unit' => 'kg',
        ]);

        $this->material2 = Material::create([
            'name' => 'Flour',
            'sku' => 'RAW-FLR-01',
            'category' => 'ingredient',
            'unit' => 'kg',
        ]);
    }

    public function test_can_generate_purchase_order()
    {
        $response = $this->post(route('purchase-orders.store'), [
            'supplier_id' => $this->supplier->id,
            'eta' => now()->addDays(5)->format('Y-m-d'),
            'notes' => 'Test PO note details.',
            'items' => [
                [
                    'material_id' => $this->material1->id,
                    'quantity' => 100,
                    'unit_price' => 45.00, // 4500
                ],
                [
                    'material_id' => $this->material2->id,
                    'quantity' => 50,
                    'unit_price' => 30.00, // 1500
                ]
            ]
        ]);

        // Should redirect to the newly created PO detail page
        $po = PurchaseOrder::first();
        $this->assertNotNull($po);
        $response->assertRedirect(route('purchase-orders.show', $po->id));

        $this->assertDatabaseHas('purchase_orders', [
            'po_number' => $po->po_number,
            'status' => 'pending',
            'total_amount' => 6000.00,
        ]);

        $this->assertDatabaseHas('purchase_order_items', [
            'purchase_order_id' => $po->id,
            'material_id' => $this->material1->id,
            'quantity' => 100.00,
            'unit_price' => 45.00,
        ]);
        
        $this->assertDatabaseHas('purchase_order_items', [
            'purchase_order_id' => $po->id,
            'material_id' => $this->material2->id,
            'quantity' => 50.00,
            'unit_price' => 30.00,
        ]);
    }

    public function test_auto_generates_unique_po_number()
    {
        $po1 = PurchaseOrder::create([
            'po_number' => 'PO-2026-0001',
            'supplier_id' => $this->supplier->id,
            'status' => 'pending',
            'total_amount' => 100.00,
        ]);

        // Generate second PO using controller
        $this->post(route('purchase-orders.store'), [
            'supplier_id' => $this->supplier->id,
            'items' => [
                [
                    'material_id' => $this->material1->id,
                    'quantity' => 10,
                    'unit_price' => 10.00,
                ]
            ]
        ]);

        $po2 = PurchaseOrder::where('id', '!=', $po1->id)->first();
        $this->assertNotNull($po2);
        
        // Assert po_number increments serial to 0002
        $year = now()->year;
        $this->assertEquals("PO-{$year}-0002", $po2->po_number);
    }
}
