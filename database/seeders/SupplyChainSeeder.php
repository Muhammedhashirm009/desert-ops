<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Supplier;
use App\Models\Material;
use App\Models\Product;
use App\Models\Outlet;
use Illuminate\Support\Facades\DB;

class SupplyChainSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create Suppliers
        $suppliers = [
            ['name' => 'Kerala Dairy Co.', 'contact_person' => 'Rajesh Kumar', 'email' => 'sales@keraladairy.com', 'phone' => '+91 9876543210', 'address' => 'Industrial Area, Kochi, Kerala 682024'],
            ['name' => 'MalabarSpice Traders', 'contact_person' => 'Fathima Noor', 'email' => 'orders@malabarspice.in', 'phone' => '+91 9845123456', 'address' => 'Spice Market, Kozhikode, Kerala 673001'],
            ['name' => 'TechPack Solutions', 'contact_person' => 'Arun Menon', 'email' => 'info@techpack.co.in', 'phone' => '+91 9012345678', 'address' => 'Packaging Zone, Thrissur, Kerala 680001'],
            ['name' => 'Premium Flour Mills', 'contact_person' => 'Suresh Nair', 'email' => 'supply@premiumflour.com', 'phone' => '+91 8899776655', 'address' => 'Mill Road, Palakkad, Kerala 678001'],
        ];

        $createdSuppliers = [];
        foreach ($suppliers as $s) {
            $createdSuppliers[] = Supplier::firstOrCreate(['email' => $s['email']], $s);
        }

        // 2. Create Materials
        $materials = [
            ['name' => 'White Sugar', 'sku' => 'RAW-SUG-001', 'category' => 'ingredient', 'unit' => 'kg', 'current_stock' => 150, 'kitchen_stock' => 50, 'min_stock_alert' => 20],
            ['name' => 'Maida Flour', 'sku' => 'RAW-FLR-001', 'category' => 'ingredient', 'unit' => 'kg', 'current_stock' => 200, 'kitchen_stock' => 80, 'min_stock_alert' => 30],
            ['name' => 'Fresh Cream', 'sku' => 'RAW-CRM-001', 'category' => 'ingredient', 'unit' => 'L', 'current_stock' => 60, 'kitchen_stock' => 25, 'min_stock_alert' => 10],
            ['name' => 'Cocoa Powder', 'sku' => 'RAW-COC-001', 'category' => 'ingredient', 'unit' => 'kg', 'current_stock' => 40, 'kitchen_stock' => 15, 'min_stock_alert' => 5],
            ['name' => 'Vanilla Extract', 'sku' => 'RAW-VAN-001', 'category' => 'ingredient', 'unit' => 'L', 'current_stock' => 10, 'kitchen_stock' => 4, 'min_stock_alert' => 2],
            ['name' => 'Butter (Unsalted)', 'sku' => 'RAW-BTR-001', 'category' => 'ingredient', 'unit' => 'kg', 'current_stock' => 80, 'kitchen_stock' => 30, 'min_stock_alert' => 15],
            ['name' => 'Dessert Box (Small)', 'sku' => 'PKG-BOX-001', 'category' => 'packaging', 'unit' => 'box', 'current_stock' => 50, 'kitchen_stock' => 20, 'min_stock_alert' => 10, 'per_box_qty' => 100, 'retail_price' => 5.00],
            ['name' => 'Dessert Box (Large)', 'sku' => 'PKG-BOX-002', 'category' => 'packaging', 'unit' => 'box', 'current_stock' => 30, 'kitchen_stock' => 12, 'min_stock_alert' => 5, 'per_box_qty' => 50, 'retail_price' => 12.00],
            ['name' => 'Paper Cup (250ml)', 'sku' => 'PKG-CUP-001', 'category' => 'packaging', 'unit' => 'box', 'current_stock' => 40, 'kitchen_stock' => 15, 'min_stock_alert' => 8, 'per_box_qty' => 200, 'retail_price' => 3.00],
        ];

        $createdMaterials = [];
        foreach ($materials as $m) {
            $createdMaterials[] = Material::firstOrCreate(['sku' => $m['sku']], $m);
        }

        // 3. Create Products
        $products = [
            ['name' => 'Chocolate Truffle Cake', 'sku' => 'DST-CTC-001', 'retail_price' => 450.00, 'current_kitchen_stock' => 25],
            ['name' => 'Vanilla Sponge Cake', 'sku' => 'DST-VSC-001', 'retail_price' => 350.00, 'current_kitchen_stock' => 30],
            ['name' => 'Red Velvet Cupcake', 'sku' => 'DST-RVC-001', 'retail_price' => 120.00, 'current_kitchen_stock' => 50],
            ['name' => 'Mango Mousse Cup', 'sku' => 'DST-MMC-001', 'retail_price' => 180.00, 'current_kitchen_stock' => 40],
            ['name' => 'Dark Chocolate Brownie', 'sku' => 'DST-DCB-001', 'retail_price' => 150.00, 'current_kitchen_stock' => 60],
            ['name' => 'Butterscotch Pastry', 'sku' => 'DST-BSP-001', 'retail_price' => 95.00, 'current_kitchen_stock' => 45],
            ['name' => 'Blueberry Cheesecake', 'sku' => 'DST-BBC-001', 'retail_price' => 520.00, 'current_kitchen_stock' => 15],
            ['name' => 'Tiramisu Cup', 'sku' => 'DST-TRM-001', 'retail_price' => 220.00, 'current_kitchen_stock' => 20],
        ];

        $createdProducts = [];
        foreach ($products as $p) {
            $createdProducts[] = Product::firstOrCreate(['sku' => $p['sku']], $p);
        }

        // 4. Create Outlets
        $outlets = [
            ['name' => 'DessertOps MG Road', 'type' => 'own', 'commission_rate' => 0, 'contact_person' => 'Priya Sharma', 'phone' => '+91 9111222333', 'address' => 'Shop 12, MG Road, Kochi 682016', 'email' => 'mgroad@dessertops.com', 'password' => 'outlet123'],
            ['name' => 'DessertOps Lulu Mall', 'type' => 'own', 'commission_rate' => 0, 'contact_person' => 'Ankit Verma', 'phone' => '+91 9222333444', 'address' => 'Unit F3, Lulu Mall, Edappally, Kochi 682024', 'email' => 'lulu@dessertops.com', 'password' => 'outlet123'],
            ['name' => 'Sweet Corner Franchise', 'type' => 'franchise', 'commission_rate' => 15.0, 'contact_person' => 'Mohammed Ali', 'phone' => '+91 9333444555', 'address' => 'Main Street, Calicut 673001', 'email' => 'sweetcorner@franchise.com', 'password' => 'outlet123'],
            ['name' => 'Cake Paradise Franchise', 'type' => 'franchise', 'commission_rate' => 12.5, 'contact_person' => 'Lakshmi Devi', 'phone' => '+91 9444555666', 'address' => 'Round South, Thrissur 680001', 'email' => 'cakeparadise@franchise.com', 'password' => 'outlet123'],
        ];

        $createdOutlets = [];
        foreach ($outlets as $o) {
            $createdOutlets[] = Outlet::firstOrCreate(['email' => $o['email']], $o);
        }

        // 5. Link Suppliers to Materials
        // Kerala Dairy → Fresh Cream, Butter
        $keralaDairy = $createdSuppliers[0];
        $keralaDairy->materials()->syncWithoutDetaching([
            $createdMaterials[2]->id => ['unit_price' => 280.00, 'is_preferred' => true, 'notes' => 'Daily fresh supply available'],
            $createdMaterials[5]->id => ['unit_price' => 420.00, 'is_preferred' => true, 'notes' => 'Amul grade unsalted'],
        ]);

        // MalabarSpice → Sugar, Cocoa, Vanilla
        $malabar = $createdSuppliers[1];
        $malabar->materials()->syncWithoutDetaching([
            $createdMaterials[0]->id => ['unit_price' => 45.00, 'is_preferred' => true, 'notes' => 'Refined white sugar'],
            $createdMaterials[3]->id => ['unit_price' => 850.00, 'is_preferred' => false, 'notes' => 'Dutch process cocoa'],
            $createdMaterials[4]->id => ['unit_price' => 1200.00, 'is_preferred' => true, 'notes' => 'Madagascar vanilla'],
        ]);

        // TechPack → All packaging items
        $techpack = $createdSuppliers[2];
        $techpack->materials()->syncWithoutDetaching([
            $createdMaterials[6]->id => ['unit_price' => 350.00, 'is_preferred' => true, 'notes' => '100 pcs/box, food grade'],
            $createdMaterials[7]->id => ['unit_price' => 480.00, 'is_preferred' => true, 'notes' => '50 pcs/box, premium finish'],
            $createdMaterials[8]->id => ['unit_price' => 220.00, 'is_preferred' => true, 'notes' => '200 pcs/box, leak-proof'],
        ]);

        // Premium Flour → Maida, Sugar (secondary supplier)
        $premiumFlour = $createdSuppliers[3];
        $premiumFlour->materials()->syncWithoutDetaching([
            $createdMaterials[1]->id => ['unit_price' => 38.00, 'is_preferred' => true, 'notes' => 'Fine grade maida flour'],
            $createdMaterials[0]->id => ['unit_price' => 48.00, 'is_preferred' => false, 'notes' => 'Backup supplier for sugar'],
        ]);

        // 6. Assign Products to Outlets
        // MG Road (own) → All products + small boxes + cups
        $mgRoad = $createdOutlets[0];
        $mgRoad->assignedProducts()->syncWithoutDetaching(collect($createdProducts)->pluck('id'));
        $mgRoad->assignedMaterials()->syncWithoutDetaching([$createdMaterials[6]->id, $createdMaterials[8]->id]);

        // Lulu Mall (own) → All products + all packaging
        $lulu = $createdOutlets[1];
        $lulu->assignedProducts()->syncWithoutDetaching(collect($createdProducts)->pluck('id'));
        $lulu->assignedMaterials()->syncWithoutDetaching([$createdMaterials[6]->id, $createdMaterials[7]->id, $createdMaterials[8]->id]);

        // Sweet Corner (franchise) → 5 products (no premium items) + small boxes
        $sweetCorner = $createdOutlets[2];
        $sweetCorner->assignedProducts()->syncWithoutDetaching([
            $createdProducts[0]->id, // Chocolate Truffle
            $createdProducts[1]->id, // Vanilla Sponge
            $createdProducts[2]->id, // Red Velvet Cupcake
            $createdProducts[4]->id, // Dark Chocolate Brownie
            $createdProducts[5]->id, // Butterscotch Pastry
        ]);
        $sweetCorner->assignedMaterials()->syncWithoutDetaching([$createdMaterials[6]->id]);

        // Cake Paradise (franchise) → 4 products + small boxes
        $cakeParadise = $createdOutlets[3];
        $cakeParadise->assignedProducts()->syncWithoutDetaching([
            $createdProducts[0]->id, // Chocolate Truffle
            $createdProducts[1]->id, // Vanilla Sponge
            $createdProducts[3]->id, // Mango Mousse
            $createdProducts[5]->id, // Butterscotch Pastry
        ]);
        $cakeParadise->assignedMaterials()->syncWithoutDetaching([$createdMaterials[6]->id]);
    }
}
