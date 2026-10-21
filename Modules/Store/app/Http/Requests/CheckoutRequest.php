<?php

namespace Modules\Store\Http\Requests;

use App\Services\Settings\SettingsService;
use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    public function rules(): array
    {
        $guestEnabled = (bool) app(SettingsService::class)->get('store.guest_checkout_enabled', false);

        return [
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['required', 'email', 'max:255'],
            'address.line1' => ['required', 'string', 'max:255'],
            'address.city' => ['required', 'string', 'max:100'],
            'address.postal_code' => ['nullable', 'string', 'max:20'],
            'address.country' => ['required', 'string', 'size:2'],
            'guest_checkout' => [$guestEnabled ? 'nullable' : 'prohibited'],
        ];
    }

    public function authorize(): bool
    {
        return (bool) app(SettingsService::class)->get('store.guest_checkout_enabled', false) || $this->user('web') !== null;
    }
}
