<?php

namespace Database\Factories;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'transaction_id' => fake()->uuid,
            'customer_email' => fake()->email(),
            'metadata' => [],
            'amount' => fake()->randomFloat(2, 100, 500),
            'card_number' => '909209123444',

        ];
    }
}
