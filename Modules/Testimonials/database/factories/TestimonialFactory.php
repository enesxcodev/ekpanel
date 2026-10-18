<?php

namespace Modules\Testimonials\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Testimonials\Models\Testimonial;

/** @extends Factory<Testimonial> */
class TestimonialFactory extends Factory
{
    protected $model = Testimonial::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'customer_name' => fake()->name(),
            'company' => fake()->company(),
            'job_title' => fake()->jobTitle(),
            'content' => '<p>'.fake()->paragraph().'</p>',
            'image' => null,
            'is_active' => false,
            'extra_fields' => [],
        ];
    }
}
