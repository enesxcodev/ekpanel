<?php

namespace Modules\Branches\Database\Factories;

use App\Models\Settings\Language;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Branches\Models\Branch;
use Modules\Branches\Models\BranchCategory;

/** @extends Factory<Branch> */
class BranchFactory extends Factory
{
    protected $model = Branch::class;

    public function definition(): array
    {
        return [
            'branch_category_id' => BranchCategory::factory(),
            'name' => fake()->company(),
            'is_active' => true,
            'sort_order' => 0,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Branch $branch): void {
            $languageId = Language::query()->where('code', 'tr')->value('id');
            if ($languageId !== null) {
                $branch->translations()->create([
                    'lang_id' => $languageId, 'name' => $branch->getRawOriginal('name'),
                    'address_detail' => $branch->getRawOriginal('address_detail'), 'phone' => $branch->getRawOriginal('phone'),
                    'email' => $branch->getRawOriginal('email'), 'website' => $branch->getRawOriginal('website'),
                    'working_hours' => $branch->getRawOriginal('working_hours'), 'map_url' => $branch->getRawOriginal('map_url'),
                ]);
            }
        });
    }
}
