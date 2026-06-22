<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductionRun;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductionRunTest extends TestCase
{
    use RefreshDatabase;

    protected $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->product = Product::create([
            'name' => 'Gulab Jamun Box',
            'sku' => 'DSR-GJB-01',
            'retail_price' => 250.00,
            'current_kitchen_stock' => 0.00,
        ]);
    }

    public function test_can_create_production_run()
    {
        $response = $this->post(route('production-runs.store'), [
            'product_id' => $this->product->id,
            'quantity_produced' => 50,
            'prepared_date' => now()->format('Y-m-d'),
            'notes' => 'Batch B1 prepared.',
        ]);

        $run = ProductionRun::first();
        $this->assertNotNull($run);
        $response->assertRedirect(route('production-runs.show', $run->id));

        $this->assertDatabaseHas('production_runs', [
            'run_number' => $run->run_number,
            'status' => 'pending',
            'quantity_produced' => 50.00,
        ]);
    }

    public function test_completing_production_run_increments_kitchen_stock()
    {
        $run = ProductionRun::create([
            'run_number' => 'PR-2026-0001',
            'product_id' => $this->product->id,
            'quantity_produced' => 100.00,
            'prepared_date' => now(),
            'status' => 'pending',
        ]);

        $response = $this->post(route('production-runs.complete', $run->id));

        $response->assertRedirect(route('production-runs.show', $run->id));
        
        $run->refresh();
        $this->assertEquals('completed', $run->status);

        // Verify product stock incremented from 0 to 100
        $this->product->refresh();
        $this->assertEquals(100.00, $this->product->current_kitchen_stock);
    }
}
