<?php

namespace Modules\Appointments\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Appointments\Models\Appointment;

class AppointmentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = Appointment::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'customer_name' => fake()->name(),
            'customer_email' => fake()->safeEmail(),
            'customer_phone' => fake()->phoneNumber(),
            'service_name' => fake()->words(2, true),
            'starts_at' => now()->addDay(),
            'duration_minutes' => 30,
            'status' => 'pending',
        ];
    }
}
