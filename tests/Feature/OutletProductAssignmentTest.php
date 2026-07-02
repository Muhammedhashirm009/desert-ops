<?php

namespace Tests\Feature;

use App\Models\Material;
use App\Models\Outlet;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OutletProductAssignmentTest extends TestCase
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

    public function test_outlet_show_page_displays_assignment_section(): void
    {
        $outlet = Outlet::factory()->create();

        $response = $this->actingAs($this->adminUser())
            ->get(route('outlets.show', $outlet->id));

        $response->assertStatus(200);
        $response->assertSee('Product');
        $response->assertSee('Packaging Assignment');
        $response->assertSee('Save Assignments');
    }

    public function test_assign_products_to_outlet(): void
    {
        $outlet = Outlet::factory()->create();
        $products = Product::factory()->count(3)->create();

        $response = $this->actingAs($this->adminUser())
            ->post(route('outlets.update-assignments', $outlet->id), [
                'product_ids' => $products->pluck('id')->toArray(),
            ]);

        $response->assertRedirect(route('outlets.show', $outlet->id));
        $this->assertEquals(3, $outlet->assignedProducts()->count());
    }

    public function test_update_assignments_replaces_previous(): void
    {
        $outlet = Outlet::factory()->create();
        $products = Product::factory()->count(3)->create();
        $outlet->assignedProducts()->sync($products->pluck('id'));

        // Now reassign only the first product
        $response = $this->actingAs($this->adminUser())
            ->post(route('outlets.update-assignments', $outlet->id), [
                'product_ids' => [$products[0]->id],
            ]);

        $response->assertRedirect();
        $this->assertEquals(1, $outlet->assignedProducts()->count());
    }

    public function test_api_assigned_products_endpoint(): void
    {
        $outlet = Outlet::factory()->create();
        $product = Product::factory()->create(['name' => 'Test Cake']);
        $outlet->assignedProducts()->attach($product->id);

        $response = $this->actingAs($this->adminUser())
            ->getJson(route('api.outlets.assigned-products', $outlet->id));

        $response->assertStatus(200);
        $response->assertJsonFragment(['name' => 'Test Cake']);
    }

    public function test_unassigned_products_not_in_api_response(): void
    {
        $outlet = Outlet::factory()->create();
        $assigned = Product::factory()->create(['name' => 'Assigned Cake']);
        $unassigned = Product::factory()->create(['name' => 'Unassigned Cake']);
        $outlet->assignedProducts()->attach($assigned->id);

        $response = $this->actingAs($this->adminUser())
            ->getJson(route('api.outlets.assigned-products', $outlet->id));

        $response->assertStatus(200);
        $response->assertJsonFragment(['name' => 'Assigned Cake']);
        $response->assertJsonMissing(['name' => 'Unassigned Cake']);
    }

    public function test_assign_packaging_materials_to_outlet(): void
    {
        $outlet = Outlet::factory()->create();
        $material = Material::factory()->create(['category' => 'packaging']);

        $response = $this->actingAs($this->adminUser())
            ->post(route('outlets.update-assignments', $outlet->id), [
                'material_ids' => [$material->id],
            ]);

        $response->assertRedirect();
        $this->assertEquals(1, $outlet->assignedMaterials()->count());
    }
}
