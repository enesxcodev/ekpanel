<?php

namespace Modules\Documents\Database\Factories;

use App\Models\Settings\Language;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Documents\Models\Document;
use Modules\Documents\Models\DocumentCategory;

class DocumentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = Document::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'category_id' => DocumentCategory::factory(),
            'file' => null,
            'status' => 'draft',
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Document $document): void {
            $language = Language::query()->where('code', 'tr')->firstOrFail();
            $document->translations()->create([
                'lang_id' => $language->id,
                'name' => fake()->sentence(),
                'slug' => fake()->unique()->slug(),
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
