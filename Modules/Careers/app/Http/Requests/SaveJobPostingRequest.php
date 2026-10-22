<?php

namespace Modules\Careers\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveJobPostingRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return ['title' => ['required', 'string', 'max:255'], 'location' => ['nullable', 'string', 'max:255'], 'employment_type' => ['required', 'in:full_time,part_time,remote,internship'], 'application_deadline' => ['nullable', 'date'], 'description' => ['nullable', 'string', 'max:20000'], 'is_active' => ['required', 'boolean']];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
