<?php

namespace Modules\Appointments\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveAppointmentRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return ['customer_name' => ['required', 'string', 'max:255'], 'customer_email' => ['nullable', 'email', 'max:255'], 'customer_phone' => ['nullable', 'string', 'max:50'], 'service_name' => ['required', 'string', 'max:255'], 'starts_at' => ['required', 'date'], 'duration_minutes' => ['required', 'integer', 'min:5', 'max:480'], 'status' => ['required', 'in:pending,confirmed,cancelled'], 'notes' => ['nullable', 'string', 'max:5000']];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
