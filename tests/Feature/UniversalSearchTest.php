<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Material;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\Outlet;
use App\Models\Product;
use App\Models\SalesLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UniversalSearchTest extends TestCase
{
    use RefreshDatabase;

    protected $shouldAuthenticate = false;

    protected $admin;
    protected $supplier;
    protected $material;
    protected $outlet;
    protected $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);

        $this->supplier = Supplier::create([
            'name' => 'Acme Supplies',
        ]);

        $this->material = Material::create([
            'name' => 'Organic Sugar Cane',
            'sku' => 'RAW-SUG-02',
            'category' => 'ingredient',
            'unit' => 'kg',
        ]);

        $this->outlet = Outlet::create([
            'name' => 'Downtown Dessert Shop',
            'type' => 'own',
            'email' => 'downtown@dessertops.com',
            'password' => bcrypt('password'),
        ]);

        $this->product = Product::create([
            'name' => 'Chocolate Fudge Cake',
            'sku' => 'FG-CHOC-01',
            'retail_price' => 250.00,
        ]);
    }

    public function test_universal_search_endpoint_returns_grouped_results()
    {
        // 1. Unauthenticated request should redirect to login
        $this->get(route('api.search', ['q' => 'Sugar']))
            ->assertRedirect(route('login'));

        // 2. Search for Material
        $response = $this->actingAs($this->admin)->get(route('api.search', ['q' => 'Sugar']));
        $response->assertStatus(200);
        $response->assertJsonFragment([
            'category' => 'Materials',
            'title' => 'Organic Sugar Cane (RAW-SUG-02)',
        ]);

        // 3. Search for Product
        $response = $this->actingAs($this->admin)->get(route('api.search', ['q' => 'Chocolate']));
        $response->assertStatus(200);
        $response->assertJsonFragment([
            'category' => 'Products',
            'title' => 'Chocolate Fudge Cake (FG-CHOC-01)',
        ]);

        // 4. Short search query returns empty array
        $response = $this->actingAs($this->admin)->get(route('api.search', ['q' => 'a']));
        $response->assertStatus(200);
        $response->assertJsonCount(0);
    }

    public function test_api_outlets_returns_ordered_list()
    {
        $response = $this->actingAs($this->admin)->get(route('api.outlets'));
        $response->assertStatus(200);
        $response->assertJsonFragment([
            'name' => 'Downtown Dessert Shop',
            'type' => 'own',
        ]);
    }

    public function test_outlet_mini_dashboard_calculates_mtd_revenue_and_stats()
    {
        // Create stock level for the outlet
        $this->outlet->stocks()->create([
            'product_id' => $this->product->id,
            'quantity' => 12.00,
        ]);

        // Create sales logs for the current month
        $salesLog = SalesLog::create([
            'outlet_id' => $this->outlet->id,
            'log_date' => now(),
        ]);

        $salesLog->items()->create([
            'product_id' => $this->product->id,
            'quantity_sold' => 3.00,
            'unit_price' => 250.00,
            'total_revenue' => 750.00,
            'net_revenue' => 750.00,
        ]);

        $response = $this->actingAs($this->admin)->get(route('outlets.mini-dashboard', $this->outlet->id));
        $response->assertStatus(200);

        // Verify MTD revenue matches sales log
        $response->assertJsonPath('mtd_revenue', 750);
        
        // Verify stock presence
        $response->assertJsonFragment([
            'name' => 'Chocolate Fudge Cake',
            'sku' => 'FG-CHOC-01',
            'quantity' => 12.00,
            'unit' => 'pcs',
        ]);

        // Verify recent sales presence
        $response->assertJsonFragment([
            'total' => 750.00,
            'items_count' => 1,
        ]);
    }
}
