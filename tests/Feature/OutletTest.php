<?php

namespace Tests\Feature;

use App\Models\Outlet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OutletTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_outlets()
    {
        Outlet::create([
            'name' => 'Kochi Mall Store',
            'type' => 'own',
            'commission_rate' => 0.00,
        ]);

        $response = $this->get(route('outlets.index'));

        $response->assertStatus(200);
        $response->assertSee('Kochi Mall Store');
        $response->assertSee('Company Owned');
    }

    public function test_can_create_own_outlet()
    {
        $response = $this->post(route('outlets.store'), [
            'name' => 'Beach Side Shop',
            'type' => 'own',
            'commission_rate' => 15.00, // should be forced to 0
            'contact_person' => 'Amal',
        ]);

        $response->assertRedirect(route('outlets.index'));
        $this->assertDatabaseHas('outlets', [
            'name' => 'Beach Side Shop',
            'type' => 'own',
            'commission_rate' => 0.00,
        ]);
    }

    public function test_can_create_franchise_outlet()
    {
        $response = $this->post(route('outlets.store'), [
            'name' => 'Thrissur Franchise',
            'type' => 'franchise',
            'commission_rate' => 12.50,
            'contact_person' => 'Rahul',
        ]);

        $response->assertRedirect(route('outlets.index'));
        $this->assertDatabaseHas('outlets', [
            'name' => 'Thrissur Franchise',
            'type' => 'franchise',
            'commission_rate' => 12.50,
        ]);
    }

    public function test_can_update_outlet()
    {
        $outlet = Outlet::create([
            'name' => 'Old Outlet',
            'type' => 'own',
            'commission_rate' => 0.00,
        ]);

        $response = $this->put(route('outlets.update', $outlet->id), [
            'name' => 'New Outlet Name',
            'type' => 'franchise',
            'commission_rate' => 10.00,
        ]);

        $response->assertRedirect(route('outlets.index'));
        $this->assertDatabaseHas('outlets', [
            'id' => $outlet->id,
            'name' => 'New Outlet Name',
            'type' => 'franchise',
            'commission_rate' => 10.00,
        ]);
    }

    public function test_can_delete_outlet()
    {
        $outlet = Outlet::create([
            'name' => 'Temp Shop',
            'type' => 'own',
            'commission_rate' => 0.00,
        ]);

        $response = $this->delete(route('outlets.destroy', $outlet->id));

        $response->assertRedirect(route('outlets.index'));
        $this->assertDatabaseMissing('outlets', [
            'id' => $outlet->id,
        ]);
    }
}
