<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'sku' => 'PRD-' . strtoupper(fake()->unique()->bothify('???-###')),
            'retail_price' => fake()->randomFloat(2, 50, 500),
            'current_kitchen_stock' => fake()->randomFloat(2, 0, 200),
        ];
    }
}
