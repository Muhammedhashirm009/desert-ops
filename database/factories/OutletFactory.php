<?php

namespace Database\Factories;

use App\Models\Outlet;
use Illuminate\Database\Eloquent\Factories\Factory;

class OutletFactory extends Factory
{
    protected $model = Outlet::class;

    public function definition(): array
    {
        return [
            'name' => fake()->company() . ' Outlet',
            'type' => fake()->randomElement(['own', 'franchise']),
            'commission_rate' => fake()->randomFloat(2, 0, 20),
            'contact_person' => fake()->name(),
            'phone' => fake()->phoneNumber(),
            'address' => fake()->address(),
            'email' => fake()->unique()->safeEmail(),
            'password' => 'password',
        ];
    }
}
