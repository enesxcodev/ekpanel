<?php

namespace Modules\Testimonials\Database\Factories;

use App\Models\Settings\Language;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Testimonials\Models\Testimonial;
use Modules\Testimonials\Models\TestimonialTranslation;

/** @extends Factory<Testimonial> */
class TestimonialFactory extends Factory
{
    protected $model = Testimonial::class;

    public function configure(): static
    {
        return $this->afterCreating(function (Testimonial $testimonial): void {
            $language = Language::query()->firstOrCreate(['code' => 'tr'], ['is_active' => true]);
            TestimonialTranslation::query()->create([
                'testimonial_id' => $testimonial->id, 'lang_id' => $language->id,
                'customer_name' => fake()->name(), 'company' => fake()->company(), 'job_title' => fake()->jobTitle(),
                'content' => '<p>'.fake()->paragraph().'</p>', 'extra_fields' => [],
            ]);
        });
    }

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'image' => null,
            'is_active' => false,
            'sort_order' => 0,
        ];
    }
}
