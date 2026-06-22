<?php

namespace Tests\Feature;

use App\Models\Material;
use App\Models\MaterialRequest;
use App\Models\MaterialRequestItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MaterialRequestTest extends TestCase
{
    use RefreshDatabase;

    protected $material1;
    protected $material2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->material1 = Material::create([
            'name' => 'Sugar',
            'sku' => 'RAW-SUG-01',
            'category' => 'ingredient',
            'unit' => 'kg',
            'current_stock' => 50.00,
            'min_stock_alert' => 10.00,
        ]);

        $this->material2 = Material::create([
            'name' => 'Milk',
            'sku' => 'RAW-MLK-01',
            'category' => 'ingredient',
            'unit' => 'L',
            'current_stock' => 100.00,
            'min_stock_alert' => 20.00,
        ]);
    }

    public function test_can_submit_material_request()
    {
        $response = $this->post(route('material-requests.store'), [
            'requested_by' => 'Chef Suresh',
            'requested_date' => now()->format('Y-m-d'),
            'notes' => 'Sugar for jamun base.',
            'items' => [
                [
                    'material_id' => $this->material1->id,
                    'quantity_requested' => 10.00,
                ],
                [
                    'material_id' => $this->material2->id,
                    'quantity_requested' => 30.00,
                ]
            ]
        ]);

        $mr = MaterialRequest::first();
        $this->assertNotNull($mr);
        $response->assertRedirect(route('material-requests.show', $mr->id));

        $this->assertDatabaseHas('material_requests', [
            'request_number' => $mr->request_number,
            'status' => 'pending',
            'requested_by' => 'Chef Suresh',
        ]);

        $this->assertDatabaseHas('material_request_items', [
            'material_request_id' => $mr->id,
            'material_id' => $this->material1->id,
            'quantity_requested' => 10.00,
        ]);
    }

    public function test_can_approve_material_request()
    {
        $mr = MaterialRequest::create([
            'request_number' => 'MR-2026-0001',
            'requested_by' => 'Chef Suresh',
            'requested_date' => now(),
            'status' => 'pending',
        ]);

        $response = $this->post(route('material-requests.approve', $mr->id));

        $response->assertRedirect(route('material-requests.show', $mr->id));
        $mr->refresh();
        $this->assertEquals('approved', $mr->status);
    }

    public function test_can_release_materials_and_decrements_stock()
    {
        $mr = MaterialRequest::create([
            'request_number' => 'MR-2026-0001',
            'requested_by' => 'Chef Suresh',
            'requested_date' => now(),
            'status' => 'approved',
        ]);

        MaterialRequestItem::create([
            'material_request_id' => $mr->id,
            'material_id' => $this->material1->id,
            'quantity_requested' => 10.00,
        ]);

        MaterialRequestItem::create([
            'material_request_id' => $mr->id,
            'material_id' => $this->material2->id,
            'quantity_requested' => 20.00,
        ]);

        $response = $this->post(route('material-requests.release', $mr->id), [
            'items' => [
                [
                    'material_id' => $this->material1->id,
                    'quantity_released' => 10.00,
                ],
                [
                    'material_id' => $this->material2->id,
                    'quantity_released' => 20.00,
                ]
            ]
        ]);

        $response->assertRedirect(route('material-requests.show', $mr->id));
        $mr->refresh();
        $this->assertEquals('released', $mr->status);

        // Verify stock decremented
        $this->material1->refresh();
        $this->material2->refresh();
        $this->assertEquals(40.00, $this->material1->current_stock); // 50 - 10
        $this->assertEquals(80.00, $this->material2->current_stock); // 100 - 20

        // Verify kitchen stock incremented
        $this->assertEquals(10.00, $this->material1->kitchen_stock); // 0 + 10
        $this->assertEquals(20.00, $this->material2->kitchen_stock); // 0 + 20
    }

    public function test_cannot_release_exceeding_stock()
    {
        $mr = MaterialRequest::create([
            'request_number' => 'MR-2026-0001',
            'requested_by' => 'Chef Suresh',
            'requested_date' => now(),
            'status' => 'approved',
        ]);

        MaterialRequestItem::create([
            'material_request_id' => $mr->id,
            'material_id' => $this->material1->id,
            'quantity_requested' => 60.00, // exceeds 50.00 stock!
        ]);

        $response = $this->post(route('material-requests.release', $mr->id), [
            'items' => [
                [
                    'material_id' => $this->material1->id,
                    'quantity_released' => 60.00,
                ]
            ]
        ]);

        $response->assertSessionHas('error');
        $this->material1->refresh();
        $this->assertEquals(50.00, $this->material1->current_stock); // stock remains unchanged
        
        $mr->refresh();
        $this->assertEquals('approved', $mr->status); // status remains approved
    }
}
