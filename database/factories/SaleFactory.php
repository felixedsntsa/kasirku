<?php

namespace Database\Factories;

use App\Models\Sale;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Sale>
 */
class SaleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 10000, 500000);

        return [
            'invoice_no' => 'INV-'.fake()->unique()->numerify('Ymd-####'),
            'user_id' => User::factory(),
            'customer_name' => fake()->optional()->name(),
            'subtotal' => $subtotal,
            'discount' => 0,
            'tax' => 0,
            'total' => $subtotal,
            'paid' => $subtotal,
            'change' => 0,
            'payment_method' => fake()->randomElement(['cash', 'qris', 'debit']),
            'status' => Sale::STATUS_PAID,
        ];
    }

    public function void(): static
    {
        return $this->state(fn (array $attributes) => ['status' => Sale::STATUS_VOID]);
    }
}
