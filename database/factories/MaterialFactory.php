<?php

namespace Database\Factories;

use App\Models\Material;
use Illuminate\Database\Eloquent\Factories\Factory;

class MaterialFactory extends Factory
{
    protected $model = Material::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'sku' => 'RAW-' . strtoupper(fake()->unique()->bothify('???-###')),
            'category' => fake()->randomElement(['ingredient', 'packaging']),
            'unit' => fake()->randomElement(['kg', 'L', 'pcs', 'box']),
            'current_stock' => fake()->randomFloat(2, 0, 500),
            'kitchen_stock' => fake()->randomFloat(2, 0, 200),
            'min_stock_alert' => fake()->randomFloat(2, 5, 50),
            'per_box_qty' => null,
            'retail_price' => null,
        ];
    }
}
