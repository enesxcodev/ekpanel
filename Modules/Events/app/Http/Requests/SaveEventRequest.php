<?php

namespace Modules\Events\Http\Requests;

use App\Models\Settings\Language;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Events\Services\EventLanguageService;

class SaveEventRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $event = $this->route('event');
        $language = is_numeric($this->input('lang_id')) ? Language::query()->find((int) $this->input('lang_id')) : null;
        if ($event && $language?->code !== EventLanguageService::PrimaryCode) {
            foreach (['starts_at', 'ends_at', 'capacity', 'is_active'] as $field) {
                $this->request->remove($field);
            }
        }

        return [
            'lang_id' => ['required', 'integer', Rule::exists('languages', 'id')->where('is_active', true), function (string $attribute, mixed $value, \Closure $fail) use ($event): void {
                if (! $event && Language::query()->whereKey($value)->value('code') !== EventLanguageService::PrimaryCode) {
                    $fail('Yeni etkinlikler Türkçe oluşturulmalıdır.');
                }
            }],
            'title' => ['required', 'string', 'max:255'],
            'venue' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:20000'],
            'starts_at' => $event && $language?->code !== EventLanguageService::PrimaryCode ? ['prohibited'] : ['required', 'date'],
            'ends_at' => $event && $language?->code !== EventLanguageService::PrimaryCode ? ['prohibited'] : ['nullable', 'date', 'after_or_equal:starts_at'],
            'capacity' => $event && $language?->code !== EventLanguageService::PrimaryCode ? ['prohibited'] : ['nullable', 'integer', 'min:1', 'max:100000'],
            'is_active' => $event && $language?->code !== EventLanguageService::PrimaryCode ? ['prohibited'] : ['required', 'boolean'],
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
