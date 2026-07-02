<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Outlet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected $shouldAuthenticate = false; // We want to test as guest

    public function test_guest_is_redirected_to_login()
    {
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_login_page_renders_successfully()
    {
        $response = $this->get(route('login'));
        $response->assertStatus(200);
        $response->assertSee('DessertOps ERP');
        $response->assertSee('Unified authentication gateway');
    }

    public function test_portal_route_redirects_to_login()
    {
        $response = $this->get('/portal');
        $response->assertRedirect(route('login'));
    }

    public function test_admin_cannot_login_with_invalid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'admin@dessertops.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->post(route('login.post'), [
            'email' => 'admin@dessertops.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_admin_can_login_with_valid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'admin@dessertops.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->post(route('login.post'), [
            'email' => 'admin@dessertops.com',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_admin_can_logout()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('logout'));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_outlet_cannot_login_with_invalid_credentials()
    {
        $outlet = Outlet::create([
            'name' => 'Kochi Store',
            'type' => 'own',
            'email' => 'kochi@dessertops.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->post(route('portal.login.post'), [
            'email' => 'kochi@dessertops.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest('outlet');
    }

    public function test_outlet_can_login_with_valid_credentials()
    {
        $outlet = Outlet::create([
            'name' => 'Kochi Store',
            'type' => 'own',
            'email' => 'kochi@dessertops.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->post(route('portal.login.post'), [
            'email' => 'kochi@dessertops.com',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('portal.dashboard'));
        $this->assertAuthenticatedAs($outlet, 'outlet');
    }

    public function test_outlet_can_logout()
    {
        $outlet = Outlet::create([
            'name' => 'Kochi Store',
            'type' => 'own',
            'email' => 'kochi@dessertops.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->actingAs($outlet, 'outlet')
            ->post(route('portal.logout'));

        $response->assertRedirect(route('portal.login'));
        $this->assertGuest('outlet');
    }
}
