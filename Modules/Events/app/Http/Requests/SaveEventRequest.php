<?php

namespace Modules\Events\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveEventRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return ['title' => ['required', 'string', 'max:255'], 'venue' => ['nullable', 'string', 'max:255'], 'starts_at' => ['required', 'date'], 'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'], 'capacity' => ['nullable', 'integer', 'min:1', 'max:100000'], 'description' => ['nullable', 'string', 'max:20000'], 'is_active' => ['required', 'boolean']];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
