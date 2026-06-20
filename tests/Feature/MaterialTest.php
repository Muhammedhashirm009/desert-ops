<?php

namespace Tests\Feature;

use App\Models\Material;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MaterialTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_materials()
    {
        Material::create([
            'name' => 'Flour',
            'sku' => 'RAW-FLR-001',
            'category' => 'ingredient',
            'unit' => 'kg',
            'current_stock' => 50,
            'min_stock_alert' => 10,
        ]);

        $response = $this->get(route('materials.index'));

        $response->assertStatus(200);
        $response->assertSee('Flour');
        $response->assertSee('RAW-FLR-001');
    }

    public function test_can_create_material()
    {
        $response = $this->post(route('materials.store'), [
            'name' => 'Packaging Wrap',
            'sku' => 'PKG-WRP-002',
            'category' => 'packaging',
            'unit' => 'pcs',
            'current_stock' => 1000,
            'min_stock_alert' => 200,
        ]);

        $response->assertRedirect(route('materials.index'));
        $this->assertDatabaseHas('materials', [
            'name' => 'Packaging Wrap',
            'sku' => 'PKG-WRP-002',
            'category' => 'packaging',
        ]);
    }

    public function test_can_update_material()
    {
        $material = Material::create([
            'name' => 'Butter',
            'sku' => 'RAW-BTR-001',
            'category' => 'ingredient',
            'unit' => 'kg',
            'current_stock' => 10,
            'min_stock_alert' => 5,
        ]);

        $response = $this->put(route('materials.update', $material->id), [
            'name' => 'Salted Butter',
            'sku' => 'RAW-BTR-001',
            'category' => 'ingredient',
            'unit' => 'kg',
            'current_stock' => 25,
            'min_stock_alert' => 5,
        ]);

        $response->assertRedirect(route('materials.index'));
        $this->assertDatabaseHas('materials', [
            'id' => $material->id,
            'name' => 'Salted Butter',
            'current_stock' => 25.00,
        ]);
    }

    public function test_detects_low_stock()
    {
        // Material 1: Normal Stock
        $m1 = Material::create([
            'name' => 'Safe Item',
            'sku' => 'RAW-SAF-001',
            'category' => 'ingredient',
            'unit' => 'kg',
            'current_stock' => 10,
            'min_stock_alert' => 5,
        ]);

        // Material 2: Low Stock
        $m2 = Material::create([
            'name' => 'Low Stock Item',
            'sku' => 'RAW-LOW-001',
            'category' => 'ingredient',
            'unit' => 'kg',
            'current_stock' => 3,
            'min_stock_alert' => 5,
        ]);

        $this->assertFalse($m1->is_low_stock);
        $this->assertTrue($m2->is_low_stock);
        
        $lowStockIds = Material::lowStock()->pluck('id')->toArray();
        $this->assertContains($m2->id, $lowStockIds);
        $this->assertNotContains($m1->id, $lowStockIds);
    }
}
