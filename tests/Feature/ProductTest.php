<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_products()
    {
        Product::create([
            'name' => 'Gulab Jamun Box',
            'sku' => 'DSR-GJB-01',
            'retail_price' => 250.00,
            'current_kitchen_stock' => 10,
        ]);

        $response = $this->get(route('products.index'));

        $response->assertStatus(200);
        $response->assertSee('Gulab Jamun Box');
        $response->assertSee('DSR-GJB-01');
    }

    public function test_can_create_product()
    {
        $response = $this->post(route('products.store'), [
            'name' => 'Mango Custard',
            'sku' => 'DSR-MGC-02',
            'retail_price' => 150.00,
            'current_kitchen_stock' => 0,
        ]);

        $response->assertRedirect(route('products.index'));
        $this->assertDatabaseHas('products', [
            'name' => 'Mango Custard',
            'sku' => 'DSR-MGC-02',
        ]);
    }

    public function test_can_update_product()
    {
        $product = Product::create([
            'name' => 'Choc Truffle',
            'sku' => 'DSR-CHT-01',
            'retail_price' => 300.00,
            'current_kitchen_stock' => 0,
        ]);

        $response = $this->put(route('products.update', $product->id), [
            'name' => 'Premium Choc Truffle',
            'sku' => 'DSR-CHT-01',
            'retail_price' => 320.00,
            'current_kitchen_stock' => 5,
        ]);

        $response->assertRedirect(route('products.index'));
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Premium Choc Truffle',
            'retail_price' => 320.00,
            'current_kitchen_stock' => 5,
        ]);
    }

    public function test_can_delete_product()
    {
        $product = Product::create([
            'name' => 'Truffle',
            'sku' => 'DSR-TRF-01',
            'retail_price' => 100.00,
            'current_kitchen_stock' => 0,
        ]);

        $response = $this->delete(route('products.destroy', $product->id));

        $response->assertRedirect(route('products.index'));
        $this->assertDatabaseMissing('products', [
            'id' => $product->id,
        ]);
    }
}
