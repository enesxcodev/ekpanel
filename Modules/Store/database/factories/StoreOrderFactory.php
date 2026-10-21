<?php

namespace Modules\Store\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Store\Models\StoreOrder;

class StoreOrderFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = StoreOrder::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return ['number' => 'STR-'.fake()->unique()->bothify('########'), 'customer_name' => fake()->name(), 'customer_email' => fake()->safeEmail(), 'shipping_address' => ['line1' => fake()->streetAddress(), 'city' => fake()->city(), 'country' => 'TR'], 'currency' => 'TRY', 'subtotal_minor' => 10000, 'tax_minor' => 0, 'shipping_minor' => 0, 'discount_minor' => 0, 'total_minor' => 10000, 'status' => 'pending', 'payment_status' => 'unpaid', 'status_history' => []];
    }
}
