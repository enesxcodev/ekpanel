<?php

namespace Modules\Store\Http\Requests;

use App\Services\Settings\SettingsService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Nwidart\Modules\Facades\Module;

class CheckoutRequest extends FormRequest
{
    public function rules(): array
    {
        $shipping = $this->input('delivery_method') !== 'pickup';
        $billing = collect($this->input('billing_address', []))->contains(fn ($value): bool => filled($value));

        return [
            'customer_name' => ['required', 'string', 'max:255'], 'customer_email' => ['required', 'email', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:30'],
            'delivery_method' => ['required', Rule::in(['shipping', 'pickup'])],
            'address' => [$shipping ? 'required' : 'nullable', 'array:line1,city,postal_code,country'],
            'address.line1' => [Rule::requiredIf($shipping), 'nullable', 'string', 'max:255'],
            'address.city' => [Rule::requiredIf($shipping), 'nullable', 'string', 'max:100'],
            'address.postal_code' => ['nullable', 'string', 'max:20'],
            'address.country' => [Rule::requiredIf($shipping), 'nullable', 'string', 'size:2'],
            'billing_address' => ['nullable', 'array:line1,city,postal_code,country'],
            'billing_address.line1' => [Rule::requiredIf($billing), 'nullable', 'string', 'max:255'],
            'billing_address.city' => [Rule::requiredIf($billing), 'nullable', 'string', 'max:100'],
            'billing_address.postal_code' => ['nullable', 'string', 'max:20'],
            'billing_address.country' => [Rule::requiredIf($billing), 'nullable', 'string', 'size:2'],
            'checkout_key' => ['required', 'uuid'], 'quote' => ['required', 'string', 'size:64'],
        ];
    }

    public function authorize(): bool
    {
        return (bool) app(SettingsService::class)->get('store.guest_checkout_enabled', false) || ((Module::find('Users')?->isEnabled() ?? false) && $this->user('web') !== null);
    }
}
