<?php

namespace Tests\Feature;

use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_suppliers()
    {
        Supplier::create([
            'name' => 'Supplier A',
            'contact_person' => 'John Doe',
        ]);

        $response = $this->get(route('suppliers.index'));

        $response->assertStatus(200);
        $response->assertSee('Supplier A');
        $response->assertSee('John Doe');
    }

    public function test_can_create_supplier()
    {
        $response = $this->post(route('suppliers.store'), [
            'name' => 'Supplier B',
            'contact_person' => 'Jane Smith',
            'email' => 'jane@supplierb.com',
            'phone' => '1234567890',
            'address' => '123 Supplier Lane',
        ]);

        $response->assertRedirect(route('suppliers.index'));
        $this->assertDatabaseHas('suppliers', [
            'name' => 'Supplier B',
            'contact_person' => 'Jane Smith',
        ]);
    }

    public function test_can_update_supplier()
    {
        $supplier = Supplier::create([
            'name' => 'Supplier C',
        ]);

        $response = $this->put(route('suppliers.update', $supplier->id), [
            'name' => 'Updated Supplier C',
            'contact_person' => 'Updated Contact',
        ]);

        $response->assertRedirect(route('suppliers.show', $supplier->id));
        $this->assertDatabaseHas('suppliers', [
            'id' => $supplier->id,
            'name' => 'Updated Supplier C',
            'contact_person' => 'Updated Contact',
        ]);
    }

    public function test_can_delete_supplier()
    {
        $supplier = Supplier::create([
            'name' => 'Supplier D',
        ]);

        $response = $this->delete(route('suppliers.destroy', $supplier->id));

        $response->assertRedirect(route('suppliers.index'));
        $this->assertDatabaseMissing('suppliers', [
            'id' => $supplier->id,
        ]);
    }
}
