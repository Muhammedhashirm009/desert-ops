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
            'showcase_quantity' => 30.00,
        ]);

        OutletStock::create([
            'outlet_id' => $this->franchiseOutlet->id,
            'product_id' => $this->product->id,
            'quantity' => 20.00,
            'showcase_quantity' => 20.00,
        ]);
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
        $stock->update([
            'quantity' => 20.00,
            'showcase_quantity' => 20.00,
        ]);

        // Delete the sales log
        $response = $this->delete(route('sales-logs.destroy', $log->id));

        $response->assertRedirect(route('sales-logs.index'));
        $this->assertNull(SalesLog::first());

        // Verify stock restored from 20 back to 30
        $stock->refresh();
        $this->assertEquals(30.00, $stock->quantity);
    }

    public function test_admin_can_access_create_sales_log_form()
    {
        $user = \App\Models\User::factory()->create(['role' => 'admin']);
        $response = $this->actingAs($user)->get(route('sales-logs.create'));
        $response->assertStatus(200);
        $response->assertSee('Log Daily Sales');
    }

    public function test_admin_can_store_sales_log()
    {
        $user = \App\Models\User::factory()->create(['role' => 'admin']);
        $response = $this->actingAs($user)->post(route('sales-logs.store'), [
            'outlet_id' => $this->ownOutlet->id,
            'log_date' => now()->format('Y-m-d'),
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity_sold' => 5,
                ]
            ]
        ]);

        $salesLog = SalesLog::first();
        $this->assertNotNull($salesLog);
        $response->assertRedirect(route('sales-logs.show', $salesLog->id));
        $this->assertDatabaseHas('sales_logs', [
            'outlet_id' => $this->ownOutlet->id,
        ]);
        $this->assertDatabaseHas('sales_log_items', [
            'product_id' => $this->product->id,
            'quantity_sold' => 5.00,
        ]);
    }
}
