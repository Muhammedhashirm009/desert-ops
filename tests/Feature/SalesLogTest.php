<?php

namespace Tests\Feature;

use App\Models\Outlet;
use App\Models\OutletStock;
use App\Models\Product;
use App\Models\SalesLog;
use App\Models\SalesLogItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalesLogTest extends TestCase
{
    use RefreshDatabase;

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
        ]);

        $this->franchiseOutlet = Outlet::create([
            'name' => 'Franchise Partner Thrissur',
            'type' => 'franchise',
            'commission_rate' => 15.00, // 15% commission
        ]);

        $this->product = Product::create([
            'name' => 'Gulab Jamun Box',
            'sku' => 'DSR-GJB-01',
            'retail_price' => 200.00,
            'current_kitchen_stock' => 50.00,
        ]);

        // Seed stock at outlets
        OutletStock::create([
            'outlet_id' => $this->ownOutlet->id,
            'product_id' => $this->product->id,
            'quantity' => 30.00,
        ]);

        OutletStock::create([
            'outlet_id' => $this->franchiseOutlet->id,
            'product_id' => $this->product->id,
            'quantity' => 20.00,
        ]);
    }

    public function test_can_log_sales_for_own_outlet()
    {
        $response = $this->post(route('sales-logs.store'), [
            'outlet_id' => $this->ownOutlet->id,
            'log_date' => now()->format('Y-m-d'),
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity_sold' => 10.00,
                ]
            ]
        ]);

        $log = SalesLog::first();
        $this->assertNotNull($log);
        $response->assertRedirect(route('sales-logs.show', $log->id));

        // Check stock decremented: 30 - 10 = 20
        $stock = OutletStock::where('outlet_id', $this->ownOutlet->id)
            ->where('product_id', $this->product->id)
            ->first();
        $this->assertEquals(20.00, $stock->quantity);

        // Check sales item totals: 10 sold * ₹200 retail = ₹2000 total revenue
        // Own outlet has 0% commission, so commission = ₹0, net = ₹2000
        $this->assertDatabaseHas('sales_log_items', [
            'sales_log_id' => $log->id,
            'product_id' => $this->product->id,
            'quantity_sold' => 10.00,
            'unit_price' => 200.00,
            'total_revenue' => 2000.00,
            'commission_amount' => 0.00,
            'net_revenue' => 2000.00,
        ]);
    }

    public function test_can_log_sales_for_franchise_outlet()
    {
        $response = $this->post(route('sales-logs.store'), [
            'outlet_id' => $this->franchiseOutlet->id,
            'log_date' => now()->format('Y-m-d'),
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity_sold' => 10.00,
                ]
            ]
        ]);

        $log = SalesLog::first();
        $this->assertNotNull($log);
        $response->assertRedirect(route('sales-logs.show', $log->id));

        // Check stock decremented: 20 - 10 = 10
        $stock = OutletStock::where('outlet_id', $this->franchiseOutlet->id)
            ->where('product_id', $this->product->id)
            ->first();
        $this->assertEquals(10.00, $stock->quantity);

        // Check sales item totals: 10 sold * ₹200 retail = ₹2000 total revenue
        // Franchise has 15% commission, so commission = ₹300, net = ₹1700
        $this->assertDatabaseHas('sales_log_items', [
            'sales_log_id' => $log->id,
            'product_id' => $this->product->id,
            'quantity_sold' => 10.00,
            'unit_price' => 200.00,
            'total_revenue' => 2000.00,
            'commission_amount' => 300.00,
            'net_revenue' => 1700.00,
        ]);
    }

    public function test_cannot_log_sales_if_insufficient_outlet_stock()
    {
        $response = $this->post(route('sales-logs.store'), [
            'outlet_id' => $this->franchiseOutlet->id,
            'log_date' => now()->format('Y-m-d'),
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity_sold' => 25.00, // exceeds outlet stock of 20
                ]
            ]
        ]);

        $response->assertSessionHas('error');
        $this->assertNull(SalesLog::first());

        // Verify stock remains 20
        $stock = OutletStock::where('outlet_id', $this->franchiseOutlet->id)
            ->where('product_id', $this->product->id)
            ->first();
        $this->assertEquals(20.00, $stock->quantity);
    }

    public function test_deleting_sales_log_restores_stock_levels()
    {
        $log = SalesLog::create([
            'outlet_id' => $this->ownOutlet->id,
            'log_date' => now(),
        ]);

        $item = SalesLogItem::create([
            'sales_log_id' => $log->id,
            'product_id' => $this->product->id,
            'quantity_sold' => 10.00,
            'unit_price' => 200.00,
            'total_revenue' => 2000.00,
            'commission_amount' => 0.00,
            'net_revenue' => 2000.00,
        ]);

        // Decrement stock to simulate logged state (originally 30, now 20)
        $stock = OutletStock::where('outlet_id', $this->ownOutlet->id)
            ->where('product_id', $this->product->id)
            ->first();
        $stock->update(['quantity' => 20.00]);

        // Delete the sales log
        $response = $this->delete(route('sales-logs.destroy', $log->id));

        $response->assertRedirect(route('sales-logs.index'));
        $this->assertNull(SalesLog::first());

        // Verify stock restored from 20 back to 30
        $stock->refresh();
        $this->assertEquals(30.00, $stock->quantity);
    }
}
