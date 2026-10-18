<?php

namespace Database\Factories\Forms;

use App\Models\Forms\SiteForm;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SiteForm>
 */
class SiteFormFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true), 'key' => fake()->unique()->slug(), 'is_active' => false, 'submit_label' => 'Gönder', 'success_message' => 'Başvurunuz alındı.',
        ];
    }
}
