<?php

namespace Database\Factories\Forms;

use App\Models\Forms\FormField;
use App\Models\Forms\SiteForm;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FormField>
 */
class FormFieldFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'site_form_id' => SiteForm::factory(), 'key' => 'message', 'label' => 'Mesaj', 'input_type' => 'textarea', 'required' => false, 'width' => 'full', 'options' => [], 'validation' => ['max' => 1000], 'sort_order' => 0,
        ];
    }
}
