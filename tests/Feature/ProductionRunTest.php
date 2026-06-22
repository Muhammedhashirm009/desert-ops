<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductionRun;
use App\Models\Material;
use App\Models\ProductionRunMaterial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductionRunTest extends TestCase
{
    use RefreshDatabase;

    protected $product;
    protected $material;

    protected function setUp(): void
    {
        parent::setUp();

        $this->product = Product::create([
            'name' => 'Gulab Jamun Box',
            'sku' => 'DSR-GJB-01',
            'retail_price' => 250.00,
            'current_kitchen_stock' => 0.00,
        ]);

        $this->material = Material::create([
            'name' => 'Sugar',
            'sku' => 'RAW-SGR-01',
            'category' => 'ingredient',
            'unit' => 'kg',
            'current_stock' => 100.00,
            'kitchen_stock' => 50.00,
            'min_stock_alert' => 10.00,
        ]);
    }

    public function test_can_create_production_run()
    {
        $response = $this->post(route('production-runs.store'), [
            'product_id' => $this->product->id,
            'quantity_produced' => 50,
            'prepared_date' => now()->format('Y-m-d'),
            'notes' => 'Batch B1 prepared.',
            'items' => [
                [
                    'material_id' => $this->material->id,
                    'quantity_used' => 10.00,
                ]
            ]
        ]);

        $run = ProductionRun::first();
        $this->assertNotNull($run);
        $response->assertRedirect(route('production-runs.show', $run->id));

        $this->assertDatabaseHas('production_runs', [
            'run_number' => $run->run_number,
            'status' => 'pending',
            'quantity_produced' => 50.00,
        ]);

        $this->assertDatabaseHas('production_run_materials', [
            'production_run_id' => $run->id,
            'material_id' => $this->material->id,
            'quantity_used' => 10.00,
        ]);
    }

    public function test_completing_production_run_increments_finished_stock_and_decrements_kitchen_stock()
    {
        $run = ProductionRun::create([
            'run_number' => 'PR-2026-0001',
            'product_id' => $this->product->id,
            'quantity_produced' => 100.00,
            'prepared_date' => now(),
            'status' => 'pending',
        ]);

        ProductionRunMaterial::create([
            'production_run_id' => $run->id,
            'material_id' => $this->material->id,
            'quantity_used' => 20.00,
        ]);

        $response = $this->post(route('production-runs.complete', $run->id));

        $response->assertRedirect(route('production-runs.show', $run->id));
        
        $run->refresh();
        $this->assertEquals('completed', $run->status);

        // Verify product stock incremented from 0 to 100
        $this->product->refresh();
        $this->assertEquals(100.00, $this->product->current_kitchen_stock);

        // Verify kitchen stock decremented from 50 to 30
        $this->material->refresh();
        $this->assertEquals(30.00, $this->material->kitchen_stock);
    }

    public function test_completing_production_run_fails_if_insufficient_kitchen_stock()
    {
        $run = ProductionRun::create([
            'run_number' => 'PR-2026-0002',
            'product_id' => $this->product->id,
            'quantity_produced' => 100.00,
            'prepared_date' => now(),
            'status' => 'pending',
        ]);

        ProductionRunMaterial::create([
            'production_run_id' => $run->id,
            'material_id' => $this->material->id,
            'quantity_used' => 60.00, // Exceeds kitchen_stock of 50.00
        ]);

        $response = $this->post(route('production-runs.complete', $run->id));

        $response->assertSessionHas('error');
        
        $run->refresh();
        $this->assertEquals('pending', $run->status);

        // Verify product stock remains 0
        $this->product->refresh();
        $this->assertEquals(0.00, $this->product->current_kitchen_stock);

        // Verify kitchen stock remains 50
        $this->material->refresh();
        $this->assertEquals(50.00, $this->material->kitchen_stock);
    }
}
