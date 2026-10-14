<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category_id' => Category::factory(),
            'supplier_id' => Supplier::factory(),
            'sku' => strtoupper(fake()->unique()->bothify('SKU-####??')),
            'barcode' => fake()->unique()->ean13(),
            'name' => fake()->unique()->words(3, true),
            'unit' => fake()->randomElement(['pcs', 'kg', 'liter', 'box']),
            'cost_price' => fake()->randomFloat(2, 1000, 50000),
            'sell_price' => fake()->randomFloat(2, 5000, 100000),
            'stock' => fake()->numberBetween(0, 100),
            'min_stock' => fake()->numberBetween(0, 10),
            'is_active' => true,
        ];
    }

    /**
     * Produk dengan stok menipis (stok <= min_stock).
     */
    public function lowStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock' => 3,
            'min_stock' => 5,
        ]);
    }
}
