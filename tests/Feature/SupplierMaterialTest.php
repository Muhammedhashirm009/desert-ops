<?php

namespace Tests\Feature;

use App\Models\Material;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierMaterialTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
    }

    private function adminUser(): User
    {
        return User::where('role', 'admin')->first();
    }

    public function test_supplier_show_page_loads(): void
    {
        $supplier = Supplier::factory()->create(['name' => 'Test Supplier']);

        $response = $this->actingAs($this->adminUser())
            ->get(route('suppliers.show', $supplier->id));

        $response->assertStatus(200);
        $response->assertSee('Test Supplier');
        $response->assertSee('Linked Materials Catalog');
    }

    public function test_link_material_to_supplier(): void
    {
        $supplier = Supplier::factory()->create();
        $material = Material::factory()->create();

        $response = $this->actingAs($this->adminUser())
            ->post(route('suppliers.link-material', $supplier->id), [
                'material_id' => $material->id,
                'unit_price' => 150.50,
                'is_preferred' => true,
                'notes' => 'Bulk rate',
            ]);

        $response->assertRedirect(route('suppliers.show', $supplier->id));
        $this->assertDatabaseHas('material_supplier', [
            'supplier_id' => $supplier->id,
            'material_id' => $material->id,
            'unit_price' => 150.50,
            'is_preferred' => true,
            'notes' => 'Bulk rate',
        ]);
    }

    public function test_unlink_material_from_supplier(): void
    {
        $supplier = Supplier::factory()->create();
        $material = Material::factory()->create();
        $supplier->materials()->attach($material->id, ['unit_price' => 100]);

        $response = $this->actingAs($this->adminUser())
            ->delete(route('suppliers.unlink-material', [$supplier->id, $material->id]));

        $response->assertRedirect(route('suppliers.show', $supplier->id));
        $this->assertDatabaseMissing('material_supplier', [
            'supplier_id' => $supplier->id,
            'material_id' => $material->id,
        ]);
    }

    public function test_supplier_index_shows_material_count(): void
    {
        $supplier = Supplier::factory()->create();
        $materials = Material::factory()->count(3)->create();
        foreach ($materials as $mat) {
            $supplier->materials()->attach($mat->id, ['unit_price' => 50]);
        }

        $response = $this->actingAs($this->adminUser())
            ->get(route('suppliers.index'));

        $response->assertStatus(200);
        $response->assertSee('3 linked');
    }

    public function test_duplicate_material_link_updates_instead(): void
    {
        $supplier = Supplier::factory()->create();
        $material = Material::factory()->create();
        $supplier->materials()->attach($material->id, ['unit_price' => 100]);

        $response = $this->actingAs($this->adminUser())
            ->post(route('suppliers.link-material', $supplier->id), [
                'material_id' => $material->id,
                'unit_price' => 200.00,
                'is_preferred' => false,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('material_supplier', [
            'supplier_id' => $supplier->id,
            'material_id' => $material->id,
            'unit_price' => 200.00,
        ]);
        // Should be only one entry, not two
        $this->assertEquals(1, $supplier->materials()->count());
    }
}
