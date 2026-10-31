<?php

namespace Modules\Faqs\Database\Factories;

use App\Models\Settings\Language;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Faqs\Models\Faq;
use Modules\Faqs\Models\FaqCategory;

class FaqFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = Faq::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'category_id' => FaqCategory::factory(),
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Faq $faq): void {
            $faq->translations()->create([
                'lang_id' => Language::query()->where('code', 'tr')->firstOrFail()->id,
                'name' => fake()->sentence(),
                'content' => fake()->paragraphs(2, true),
                'extra_fields' => [],
            ]);
        });
    }

    public function uncategorized(): static
    {
        return $this->state(['category_id' => null]);
    }
}
