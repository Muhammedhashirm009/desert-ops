<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Material;
use App\Models\Product;
use App\Models\Outlet;
use App\Models\Account;
use App\Models\JournalTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryInitializationTest extends TestCase
{
    use RefreshDatabase;
    protected $shouldAuthenticate = false;

    private $user;
    private $material;
    private $product;
    private $outlet;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->material = Material::create([
            'name' => 'Flour',
            'sku' => 'MAT-FLOUR',
            'category' => 'ingredient',
            'unit' => 'kg',
            'current_stock' => 0,
            'kitchen_stock' => 0,
            'cost_price' => 0,
        ]);

        $this->product = Product::create([
            'name' => 'Cake',
            'sku' => 'PROD-CAKE',
            'retail_price' => 10.00,
            'cost_price' => 0,
            'current_kitchen_stock' => 0,
        ]);

        $this->outlet = Outlet::create([
            'name' => 'Downtown Outlet',
            'type' => 'own',
            'email' => 'downtown@dessertops.com',
            'password' => bcrypt('password'),
        ]);

        // Attach product to outlet
        $this->outlet->assignedProducts()->attach($this->product->id, ['type' => 'pre_cooked']);

        // Set up accounts
        Account::create(['code' => '1300', 'name' => 'Inventory', 'type' => 'asset']);
        Account::create(['code' => '3000', 'name' => 'Equity', 'type' => 'equity']);
    }

    public function test_guest_is_redirected()
    {
        $response = $this->get(route('inventory.initialize'));
        $response->assertRedirect(route('login'));
    }

    public function test_admin_can_access_initialization_page()
    {
        $response = $this->actingAs($this->user)
            ->get(route('inventory.initialize'));

        $response->assertStatus(200);
        $response->assertSee('MAT-FLOUR');
        $response->assertSee('PROD-CAKE');
        $response->assertSee('Downtown Outlet');
    }

    public function test_admin_can_initialize_stock_and_cost()
    {
        $response = $this->actingAs($this->user)
            ->post(route('inventory.initialize.store'), [
                'materials' => [
                    [
                        'id' => $this->material->id,
                        'current_stock' => 50.00,
                        'kitchen_stock' => 10.00,
                        'cost_price' => 5.00,
                    ]
                ],
                'products' => [
                    [
                        'id' => $this->product->id,
                        'current_kitchen_stock' => 20.00,
                        'cost_price' => 8.00,
                    ]
                ],
                'outlet_stocks' => [
                    [
                        'outlet_id' => $this->outlet->id,
                        'product_id' => $this->product->id,
                        'outlet_catalog_item_id' => null,
                        'store_quantity' => 5.00,
                        'kitchen_quantity' => 2.00,
                        'showcase_quantity' => 3.00,
                    ]
                ]
            ]);

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('success');

        // Check central inventory stock updates
        $this->material->refresh();
        $this->assertEquals(50.00, $this->material->current_stock);
        $this->assertEquals(10.00, $this->material->kitchen_stock);
        $this->assertEquals(5.00, $this->material->cost_price);

        $this->product->refresh();
        $this->assertEquals(20.00, $this->product->current_kitchen_stock);
        $this->assertEquals(8.00, $this->product->cost_price);

        // Check outlet inventory stock updates
        $this->assertDatabaseHas('outlet_stocks', [
            'outlet_id' => $this->outlet->id,
            'product_id' => $this->product->id,
            'store_quantity' => 5.00,
            'kitchen_quantity' => 2.00,
            'showcase_quantity' => 3.00,
            'quantity' => 10.00,
        ]);

        // Check price history logging
        $this->assertDatabaseHas('price_histories', [
            'item_type' => 'material',
            'item_id' => $this->material->id,
            'new_cost_price' => 5.00,
        ]);
        $this->assertDatabaseHas('price_histories', [
            'item_type' => 'product',
            'item_id' => $this->product->id,
            'new_cost_price' => 8.00,
        ]);

        // Check accounting starting balance journal entry:
        // Raw materials value: (50 + 10) * 5 = 300
        // Product value: 20 * 8 = 160
        // Total value = 460
        $tx = JournalTransaction::where('reference', 'like', 'SYS-INIT-%')->first();
        $this->assertNotNull($tx);
        $this->assertDatabaseHas('journal_entries', [
            'journal_transaction_id' => $tx->id,
            'account_id' => Account::where('code', '1300')->first()->id,
            'debit' => 460.00,
        ]);
        $this->assertDatabaseHas('journal_entries', [
            'journal_transaction_id' => $tx->id,
            'account_id' => Account::where('code', '3000')->first()->id,
            'credit' => 460.00,
        ]);
    }
}
