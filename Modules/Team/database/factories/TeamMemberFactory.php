<?php

namespace Modules\Team\Database\Factories;

use App\Models\Settings\Language;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Team\Models\TeamMember;

/** @extends Factory<TeamMember> */
class TeamMemberFactory extends Factory
{
    protected $model = TeamMember::class;

    public function definition(): array
    {
        return ['image' => null, 'is_active' => true];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (TeamMember $member): void {
            $language = Language::query()->firstOrCreate(['code' => 'tr'], ['is_active' => true]);
            if (! $language->is_active) {
                $language->forceFill(['is_active' => true])->save();
            }
            $member->translations()->firstOrCreate(['lang_id' => $language->id], [
                'name' => fake()->name(),
                'title' => fake()->jobTitle(),
                'extra_fields' => [],
            ]);
        });
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}
