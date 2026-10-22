<?php

namespace Modules\Careers\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveJobApplicationRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'job_posting_id' => ['required', 'integer', 'exists:job_postings,id'],
            'candidate_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'cover_letter' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', 'in:new,reviewing,accepted,rejected'],
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
