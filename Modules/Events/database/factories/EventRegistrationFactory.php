<?php

namespace Modules\Events\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Events\Models\Event;
use Modules\Events\Models\EventRegistration;

class EventRegistrationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = EventRegistration::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'attendee_name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'status' => 'registered',
        ];
    }
}
