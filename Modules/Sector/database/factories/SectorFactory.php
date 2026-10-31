<?php

namespace Modules\Sector\Database\Factories;

use App\Models\Settings\Language;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Sector\Models\Sector;

/** @extends Factory<Sector> */
class SectorFactory extends Factory
{
    protected $model = Sector::class;

    public function definition(): array
    {
        return [
            'image' => null,
            'image_media_id' => null,
            'sort_order' => null,
            'is_active' => true,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Sector $sector): void {
            $language = Language::query()->firstOrCreate(['code' => 'tr'], ['is_active' => true]);
            $sector->translations()->create([
                'lang_id' => $language->id,
                'title' => fake()->words(3, true),
                'extra_fields' => [],
            ]);
        });
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}
