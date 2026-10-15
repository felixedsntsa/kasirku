<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StockMovement>
 */
class StockMovementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'type' => StockMovement::TYPE_IN,
            'qty' => fake()->numberBetween(1, 50),
            'note' => fake()->sentence(),
            'user_id' => User::factory(),
        ];
    }

    public function in(): static
    {
        return $this->state(fn (array $attributes) => ['type' => StockMovement::TYPE_IN]);
    }

    public function out(): static
    {
        return $this->state(fn (array $attributes) => ['type' => StockMovement::TYPE_OUT]);
    }

    public function adjust(): static
    {
        return $this->state(fn (array $attributes) => ['type' => StockMovement::TYPE_ADJUST]);
    }
}
