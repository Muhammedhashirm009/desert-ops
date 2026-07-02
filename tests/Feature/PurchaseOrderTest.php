<?php

namespace Tests\Feature;

use App\Models\Material;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\User;
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
        $user = User::factory()->create(['role' => 'admin']);
        $response = $this->actingAs($user)->post(route('purchase-orders.store'), [
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
        $user = User::factory()->create(['role' => 'admin']);
        $po1 = PurchaseOrder::create([
            'po_number' => 'PO-2026-0001',
            'supplier_id' => $this->supplier->id,
            'status' => 'pending',
            'total_amount' => 100.00,
        ]);

        // Generate second PO using controller
        $this->actingAs($user)->post(route('purchase-orders.store'), [
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

    public function test_purchase_order_show_page_contains_whatsapp_send_link()
    {
        $user = User::factory()->create(['role' => 'admin']);
        
        $this->supplier->update(['phone' => '+91 98765 43210']);
        
        $po = PurchaseOrder::create([
            'po_number' => 'PO-2026-9999',
            'supplier_id' => $this->supplier->id,
            'status' => 'pending',
            'total_amount' => 7500.00,
            'notes' => 'Urgent shipment requested.',
        ]);

        $po->items()->create([
            'material_id' => $this->material1->id,
            'quantity' => 100,
            'unit_price' => 50.00,
            'subtotal' => 5000.00,
        ]);

        $po->items()->create([
            'material_id' => $this->material2->id,
            'quantity' => 50,
            'unit_price' => 50.00,
            'subtotal' => 2500.00,
        ]);

        $response = $this->actingAs($user)->get(route('purchase-orders.show', $po->id));

        $response->assertStatus(200);
        $response->assertSee('api.whatsapp.com/send?phone=919876543210&text=');
        $response->assertSee('Copy PO Text');
        $response->assertSee('PO-2026-9999');
        $response->assertSee('Test Supplier');
        $response->assertSee('Urgent shipment requested');
        $response->assertSee('Sugar');
        $response->assertSee('Flour');
    }

    public function test_can_create_multiple_pos_at_once()
    {
        $user = User::factory()->create(['role' => 'admin']);
        
        $supplierB = Supplier::create([
            'name' => 'Supplier B',
        ]);

        $response = $this->actingAs($user)->post(route('purchase-orders.store'), [
            'eta' => now()->addDays(5)->format('Y-m-d'),
            'notes' => 'Test multiple PO split.',
            'items' => [
                [
                    'material_id' => $this->material1->id,
                    'supplier_id' => $this->supplier->id,
                    'quantity' => 10,
                    'unit_price' => 10.00,
                ],
                [
                    'material_id' => $this->material2->id,
                    'supplier_id' => $supplierB->id,
                    'quantity' => 20,
                    'unit_price' => 15.00,
                ]
            ]
        ]);

        // Should redirect to index since multiple POs were generated
        $response->assertRedirect(route('purchase-orders.index'));
        $response->assertSessionHas('success');

        // Check if two POs are generated
        $this->assertEquals(2, PurchaseOrder::count());

        $po1 = PurchaseOrder::where('supplier_id', $this->supplier->id)->first();
        $this->assertNotNull($po1);
        $this->assertEquals(100.00, $po1->total_amount);

        $po2 = PurchaseOrder::where('supplier_id', $supplierB->id)->first();
        $this->assertNotNull($po2);
        $this->assertEquals(300.00, $po2->total_amount);
    }
}
