<?php

namespace Modules\Branches\Database\Factories;

use App\Models\Settings\Language;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Branches\Models\BranchCategory;

/** @extends Factory<BranchCategory> */
class BranchCategoryFactory extends Factory
{
    protected $model = BranchCategory::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return ['slug' => Str::slug($name), 'name' => $name, 'is_active' => true, 'sort_order' => 0];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (BranchCategory $category): void {
            $languageId = Language::query()->where('code', 'tr')->value('id');
            if ($languageId !== null) {
                $category->translations()->create([
                    'lang_id' => $languageId, 'name' => $category->getRawOriginal('name'),
                ]);
            }
        });
    }
}
