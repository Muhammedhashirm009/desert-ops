<?php

namespace Tests\Feature;

use App\Models\Material;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\GoodsReceivedNote;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GrnTest extends TestCase
{
    use RefreshDatabase;

    protected $supplier;
    protected $material1;
    protected $material2;
    protected $purchaseOrder;

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
            'current_stock' => 10.00,
            'min_stock_alert' => 5.00,
        ]);

        $this->material2 = Material::create([
            'name' => 'Flour',
            'sku' => 'RAW-FLR-01',
            'category' => 'ingredient',
            'unit' => 'kg',
            'current_stock' => 20.00,
            'min_stock_alert' => 5.00,
        ]);

        // Create a PO
        $this->purchaseOrder = PurchaseOrder::create([
            'po_number' => 'PO-2026-0001',
            'supplier_id' => $this->supplier->id,
            'status' => 'pending',
            'total_amount' => 1000.00,
        ]);

        PurchaseOrderItem::create([
            'purchase_order_id' => $this->purchaseOrder->id,
            'material_id' => $this->material1->id,
            'quantity' => 10.00,
            'unit_price' => 50.00,
        ]);

        PurchaseOrderItem::create([
            'purchase_order_id' => $this->purchaseOrder->id,
            'material_id' => $this->material2->id,
            'quantity' => 25.00,
            'unit_price' => 20.00,
        ]);
    }

    public function test_can_receive_purchase_order_goods()
    {
        $response = $this->post(route('purchase-orders.receive.store', $this->purchaseOrder->id), [
            'received_by' => 'Staff Receiver',
            'received_date' => now()->format('Y-m-d'),
            'notes' => 'Delivered in full.',
            'items' => [
                [
                    'material_id' => $this->material1->id,
                    'quantity_received' => 10.00, // received full amount
                ],
                [
                    'material_id' => $this->material2->id,
                    'quantity_received' => 25.00, // received full amount
                ]
            ]
        ]);

        $grn = GoodsReceivedNote::first();
        $this->assertNotNull($grn);
        $response->assertRedirect(route('grns.show', $grn->id));

        // Verify status updates
        $this->purchaseOrder->refresh();
        $this->assertEquals('received', $this->purchaseOrder->status);

        // Verify GRN DB entries
        $this->assertDatabaseHas('goods_received_notes', [
            'grn_number' => $grn->grn_number,
            'purchase_order_id' => $this->purchaseOrder->id,
            'received_by' => 'Staff Receiver',
        ]);

        $this->assertDatabaseHas('goods_received_note_items', [
            'goods_received_note_id' => $grn->id,
            'material_id' => $this->material1->id,
            'quantity_received' => 10.00,
        ]);

        $this->assertDatabaseHas('goods_received_note_items', [
            'goods_received_note_id' => $grn->id,
            'material_id' => $this->material2->id,
            'quantity_received' => 25.00,
        ]);

        // Verify stock levels incremented
        $this->material1->refresh();
        $this->material2->refresh();
        
        // Initial was 10.00 + 10.00 received = 20.00
        $this->assertEquals(20.00, $this->material1->current_stock);
        // Initial was 20.00 + 25.00 received = 45.00
        $this->assertEquals(45.00, $this->material2->current_stock);
    }
}
