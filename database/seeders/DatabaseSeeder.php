<?php

namespace Database\Seeders;

use App\Models\Supplier;
use App\Models\Material;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\GoodsReceivedNote;
use App\Models\GoodsReceivedNoteItem;
use App\Models\User;
use App\Models\Product;
use App\Models\MaterialRequest;
use App\Models\MaterialRequestItem;
use App\Models\ProductionRun;
use App\Models\ProductionRunMaterial;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Default Admin User
        User::factory()->create([
            'name' => 'Hashir',
            'email' => 'admin@dessertops.com',
            'password' => bcrypt('password'),
        ]);

        // 2. Create Suppliers
        $keralaDairy = Supplier::create([
            'name' => 'Kerala Dairy Co.',
            'contact_person' => 'Rajesh Kumar',
            'email' => 'sales@keraladairy.com',
            'phone' => '+91 9876543210',
            'address' => "Milk Colony Road, Calicut, Kerala\nPIN: 673001",
        ]);

        $malabarSugar = Supplier::create([
            'name' => 'Malabar Sugar Ltd.',
            'contact_person' => 'Abdul Hameed',
            'email' => 'info@malabarsugar.in',
            'phone' => '+91 9847012345',
            'address' => "Sugar Mill Compound, Palakkad, Kerala\nPIN: 678002",
        ]);

        $fruitHub = Supplier::create([
            'name' => 'Fresh Fruits Hub',
            'contact_person' => 'Anil Kumar',
            'email' => 'orders@freshfruithub.com',
            'phone' => '+91 9446055555',
            'address' => "Market Junction, Kochi, Kerala\nPIN: 682011",
        ]);

        $packingWorld = Supplier::create([
            'name' => 'Packing World',
            'contact_person' => 'Shaji V.G.',
            'email' => 'support@packingworld.co.in',
            'phone' => '+91 9562098765',
            'address' => "Industrial Estate, Ollur, Thrissur, Kerala\nPIN: 680306",
        ]);

        $cocoaImports = Supplier::create([
            'name' => 'Cocoa Imports Pvt.',
            'contact_person' => 'Maria Jones',
            'email' => 'maria@cocoaimports.in',
            'phone' => '+91 8023456789',
            'address' => "Port Trust Area, Wellington Island, Kochi, Kerala\nPIN: 682003",
        ]);

        // 3. Create Materials (Ingredients & Packaging)
        $milk = Material::create([
            'name' => 'Milk',
            'sku' => 'RAW-MLK-001',
            'category' => 'ingredient',
            'unit' => 'L',
            'current_stock' => 350.00,
            'kitchen_stock' => 80.00,
            'min_stock_alert' => 100.00,
        ]);

        $cream = Material::create([
            'name' => 'Fresh Cream',
            'sku' => 'RAW-CRM-002',
            'category' => 'ingredient',
            'unit' => 'L',
            'current_stock' => 80.00,
            'kitchen_stock' => 30.00,
            'min_stock_alert' => 20.00,
        ]);

        $sugar = Material::create([
            'name' => 'White Sugar',
            'sku' => 'RAW-SUG-001',
            'category' => 'ingredient',
            'unit' => 'kg',
            'current_stock' => 450.00,
            'kitchen_stock' => 100.00,
            'min_stock_alert' => 150.00,
        ]);

        $cocoa = Material::create([
            'name' => 'Dark Cocoa Powder',
            'sku' => 'RAW-CCA-001',
            'category' => 'ingredient',
            'unit' => 'kg',
            'current_stock' => 25.00,
            'kitchen_stock' => 15.00,
            'min_stock_alert' => 10.00,
        ]);

        $butter = Material::create([
            'name' => 'Butter',
            'sku' => 'RAW-BTR-001',
            'category' => 'ingredient',
            'unit' => 'kg',
            'current_stock' => 75.00,
            'kitchen_stock' => 20.00,
            'min_stock_alert' => 25.00,
        ]);

        $strawberries = Material::create([
            'name' => 'Mango Pulp',
            'sku' => 'RAW-FRT-MNG',
            'category' => 'ingredient',
            'unit' => 'kg',
            'current_stock' => 65.00,
            'kitchen_stock' => 25.00,
            'min_stock_alert' => 20.00,
        ]);

        $strawberryFresh = Material::create([
            'name' => 'Strawberry',
            'sku' => 'RAW-FRT-STR',
            'category' => 'ingredient',
            'unit' => 'kg',
            'current_stock' => 15.00,
            'kitchen_stock' => 10.00,
            'min_stock_alert' => 5.00,
        ]);

        $jamunBase = Material::create([
            'name' => 'Gulab Jamun Base',
            'sku' => 'RAW-BASE-GJ',
            'category' => 'ingredient',
            'unit' => 'kg',
            'current_stock' => 3.50, // Critical stock!
            'kitchen_stock' => 15.00,
            'min_stock_alert' => 10.00,
        ]);

        $boxes = Material::create([
            'name' => 'Dessert Boxes',
            'sku' => 'PKG-BOX-MED',
            'category' => 'packaging',
            'unit' => 'pcs',
            'current_stock' => 1200.00,
            'kitchen_stock' => 150.00,
            'min_stock_alert' => 300.00,
        ]);

        $wrappers = Material::create([
            'name' => 'Wrappers',
            'sku' => 'PKG-WRP-001',
            'category' => 'packaging',
            'unit' => 'pcs',
            'current_stock' => 5000.00,
            'kitchen_stock' => 500.00,
            'min_stock_alert' => 1000.00,
        ]);

        $labels = Material::create([
            'name' => 'Sticky Labels',
            'sku' => 'PKG-LBL-001',
            'category' => 'packaging',
            'unit' => 'pcs',
            'current_stock' => 120.00, // Critical stock!
            'kitchen_stock' => 50.00,
            'min_stock_alert' => 500.00,
        ]);

        // 4. Create Purchase Orders (POs)
        // PO #1 (Received)
        $po1 = PurchaseOrder::create([
            'po_number' => 'PO-2026-0001',
            'supplier_id' => $keralaDairy->id,
            'status' => 'received',
            'total_amount' => 18400.00,
            'notes' => 'Urgent replenishment of dairy stocks.',
            'eta' => Carbon::now()->subDays(2),
            'created_at' => Carbon::now()->subDays(3),
        ]);
        PurchaseOrderItem::create([
            'purchase_order_id' => $po1->id,
            'material_id' => $milk->id,
            'quantity' => 200,
            'unit_price' => 60.00, // 12000
        ]);
        PurchaseOrderItem::create([
            'purchase_order_id' => $po1->id,
            'material_id' => $cream->id,
            'quantity' => 80,
            'unit_price' => 80.00, // 6400
        ]);

        // PO #2 (In Transit / Pending)
        $po2 = PurchaseOrder::create([
            'po_number' => 'PO-2026-0002',
            'supplier_id' => $malabarSugar->id,
            'status' => 'pending',
            'total_amount' => 9200.00,
            'notes' => 'Standard monthly sugar refill.',
            'eta' => Carbon::now()->addDays(2),
            'created_at' => Carbon::now()->subDays(1),
        ]);
        PurchaseOrderItem::create([
            'purchase_order_id' => $po2->id,
            'material_id' => $sugar->id,
            'quantity' => 200,
            'unit_price' => 46.00,
        ]);

        // PO #3 (Pending Approval)
        $po3 = PurchaseOrder::create([
            'po_number' => 'PO-2026-0003',
            'supplier_id' => $fruitHub->id,
            'status' => 'pending',
            'total_amount' => 22750.00,
            'notes' => 'For mango and strawberry dessert menu launch.',
            'eta' => Carbon::now()->addDays(4),
            'created_at' => Carbon::now()->subHours(5),
        ]);
        PurchaseOrderItem::create([
            'purchase_order_id' => $po3->id,
            'material_id' => $strawberryFresh->id,
            'quantity' => 50,
            'unit_price' => 250.00, // 12500
        ]);
        PurchaseOrderItem::create([
            'purchase_order_id' => $po3->id,
            'material_id' => $strawberries->id,
            'quantity' => 150,
            'unit_price' => 68.33, // 10250 (roughly matches 22750 total)
        ]);

        // PO #4 (Pending Approval)
        $po4 = PurchaseOrder::create([
            'po_number' => 'PO-2026-0004',
            'supplier_id' => $packingWorld->id,
            'status' => 'pending',
            'total_amount' => 5600.00,
            'notes' => 'Reordering labels to resolve low stock warning.',
            'eta' => Carbon::now()->addDays(3),
            'created_at' => Carbon::now()->subHours(2),
        ]);
        PurchaseOrderItem::create([
            'purchase_order_id' => $po4->id,
            'material_id' => $boxes->id,
            'quantity' => 500,
            'unit_price' => 8.00, // 4000
        ]);
        PurchaseOrderItem::create([
            'purchase_order_id' => $po4->id,
            'material_id' => $labels->id,
            'quantity' => 800,
            'unit_price' => 2.00, // 1600
        ]);

        // PO #5 (Received)
        $po5 = PurchaseOrder::create([
            'po_number' => 'PO-2026-0005',
            'supplier_id' => $cocoaImports->id,
            'status' => 'received',
            'total_amount' => 31000.00,
            'notes' => 'Premium dark cocoa and fat supplies.',
            'eta' => Carbon::now()->subDays(1),
            'created_at' => Carbon::now()->subDays(4),
        ]);
        PurchaseOrderItem::create([
            'purchase_order_id' => $po5->id,
            'material_id' => $cocoa->id,
            'quantity' => 50,
            'unit_price' => 400.00, // 20000
        ]);
        PurchaseOrderItem::create([
            'purchase_order_id' => $po5->id,
            'material_id' => $butter->id,
            'quantity' => 55,
            'unit_price' => 200.00, // 11000
        ]);

        // 5. Create Goods Received Notes (GRNs)
        // GRN #1 (for PO #1)
        $grn1 = GoodsReceivedNote::create([
            'grn_number' => 'GRN-2026-0001',
            'purchase_order_id' => $po1->id,
            'received_date' => Carbon::now()->subDays(2)->setHour(8)->setMinute(45),
            'received_by' => 'Hashir',
            'notes' => 'All items received in good condition. Temperature logs checked.',
            'created_at' => Carbon::now()->subDays(2)->setHour(8)->setMinute(45),
        ]);
        GoodsReceivedNoteItem::create([
            'goods_received_note_id' => $grn1->id,
            'material_id' => $milk->id,
            'quantity_received' => 200.00,
        ]);
        GoodsReceivedNoteItem::create([
            'goods_received_note_id' => $grn1->id,
            'material_id' => $cream->id,
            'quantity_received' => 80.00,
        ]);

        // GRN #2 (for PO #5)
        $grn2 = GoodsReceivedNote::create([
            'grn_number' => 'GRN-2026-0002',
            'purchase_order_id' => $po5->id,
            'received_date' => Carbon::now()->subDays(1)->setHour(10)->setMinute(15),
            'received_by' => 'Hashir',
            'notes' => 'Butter slightly soft but acceptable. Cocoa sealed.',
            'created_at' => Carbon::now()->subDays(1)->setHour(10)->setMinute(15),
        ]);
        GoodsReceivedNoteItem::create([
            'goods_received_note_id' => $grn2->id,
            'material_id' => $cocoa->id,
            'quantity_received' => 50.00,
        ]);
        GoodsReceivedNoteItem::create([
            'goods_received_note_id' => $grn2->id,
            'material_id' => $butter->id,
            'quantity_received' => 55.00,
        ]);
        // 6. Create Dessert Products Catalog
        $gjBox = Product::create([
            'name' => 'Gulab Jamun Box',
            'sku' => 'DSR-GJB-001',
            'retail_price' => 250.00,
            'current_kitchen_stock' => 120.00, // Stock will be updated by PR-0001
        ]);

        $mangoCustard = Product::create([
            'name' => 'Mango Custard',
            'sku' => 'DSR-MGC-002',
            'retail_price' => 150.00,
            'current_kitchen_stock' => 80.00, // Stock updated by PR-0002
        ]);

        $chocTruffle = Product::create([
            'name' => 'Chocolate Truffle',
            'sku' => 'DSR-CHT-003',
            'retail_price' => 300.00,
            'current_kitchen_stock' => 0.00,
        ]);

        // 7. Create Material Requests
        // Request #1 (Released)
        $mr1 = MaterialRequest::create([
            'request_number' => 'MR-2026-0001',
            'requested_by' => 'Chef Suresh',
            'requested_date' => Carbon::now()->subDays(2),
            'status' => 'released',
            'notes' => 'For base preparation of Gulab Jamun batch.',
            'created_at' => Carbon::now()->subDays(2),
        ]);
        MaterialRequestItem::create([
            'material_request_id' => $mr1->id,
            'material_id' => $milk->id,
            'quantity_requested' => 50.00,
            'quantity_released' => 50.00,
        ]);
        MaterialRequestItem::create([
            'material_request_id' => $mr1->id,
            'material_id' => $sugar->id,
            'quantity_requested' => 20.00,
            'quantity_released' => 20.00,
        ]);

        // Request #2 (Pending)
        $mr2 = MaterialRequest::create([
            'request_number' => 'MR-2026-0002',
            'requested_by' => 'Head Baker Maria',
            'requested_date' => Carbon::now(),
            'status' => 'pending',
            'notes' => 'Ingredients for Chocolate Truffle batch. Urgent release requested.',
            'created_at' => Carbon::now(),
        ]);
        MaterialRequestItem::create([
            'material_request_id' => $mr2->id,
            'material_id' => $cocoa->id,
            'quantity_requested' => 10.00,
            'quantity_released' => 0.00,
        ]);
        MaterialRequestItem::create([
            'material_request_id' => $mr2->id,
            'material_id' => $butter->id,
            'quantity_requested' => 15.00,
            'quantity_released' => 0.00,
        ]);

        // 8. Create Production Runs
        // Run #1 (Completed today)
        $pr1 = ProductionRun::create([
            'run_number' => 'PR-2026-0001',
            'product_id' => $gjBox->id,
            'quantity_produced' => 120.00,
            'prepared_date' => Carbon::now(),
            'status' => 'completed',
            'notes' => 'Completed morning shift run. Packed and ready for distribution.',
            'created_at' => Carbon::now(),
        ]);
        ProductionRunMaterial::create([
            'production_run_id' => $pr1->id,
            'material_id' => $milk->id,
            'quantity_used' => 40.00,
        ]);
        ProductionRunMaterial::create([
            'production_run_id' => $pr1->id,
            'material_id' => $sugar->id,
            'quantity_used' => 15.00,
        ]);
        ProductionRunMaterial::create([
            'production_run_id' => $pr1->id,
            'material_id' => $jamunBase->id,
            'quantity_used' => 10.00,
        ]);

        // Run #2 (Completed yesterday)
        $pr2 = ProductionRun::create([
            'run_number' => 'PR-2026-0002',
            'product_id' => $mangoCustard->id,
            'quantity_produced' => 80.00,
            'prepared_date' => Carbon::now()->subDay(),
            'status' => 'completed',
            'notes' => 'Batch #M04 tasted perfect. Cold storage stored.',
            'created_at' => Carbon::now()->subDay(),
        ]);
        ProductionRunMaterial::create([
            'production_run_id' => $pr2->id,
            'material_id' => $milk->id,
            'quantity_used' => 25.00,
        ]);
        ProductionRunMaterial::create([
            'production_run_id' => $pr2->id,
            'material_id' => $sugar->id,
            'quantity_used' => 10.00,
        ]);
        ProductionRunMaterial::create([
            'production_run_id' => $pr2->id,
            'material_id' => $strawberries->id,
            'quantity_used' => 12.00,
        ]);
    }
}

