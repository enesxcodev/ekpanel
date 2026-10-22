<?php

namespace Modules\Store\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStoreOrderRequest extends FormRequest
{
    public function rules(): array
    {
        return ['status' => ['required', 'in:pending,confirmed,preparing,ready,shipped,completed,cancelled'], 'payment_status' => ['required', 'in:unpaid,paid,refunded'], 'tracking_number' => ['nullable', 'string', 'max:255'], 'note' => ['nullable', 'string', 'max:1000']];
    }

    public function authorize(): bool
    {
        return $this->user('admin')?->can('store.orders.update') ?? false;
    }
}
