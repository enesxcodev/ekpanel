<?php

namespace Database\Factories\Forms;

use App\Models\Forms\FormSubmission;
use App\Models\Forms\SiteForm;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FormSubmission>
 */
class FormSubmissionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'site_form_id' => SiteForm::factory(), 'form_name' => 'İletişim', 'status' => 'new', 'answers' => ['message' => ['label' => 'Mesaj', 'type' => 'textarea', 'value' => fake()->sentence()]],
        ];
    }
}
