<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Settings\SaveLanguageRequest;
use App\Models\Settings\Language;
use Illuminate\Http\RedirectResponse;

class LanguageController extends Controller
{
    public function store(SaveLanguageRequest $request): RedirectResponse
    {
        Language::query()->firstOrCreate(['code' => $request->validated('code')], ['is_active' => true]);

        return redirect()->route('admin.settings.index', ['section' => 'languages'])
            ->with('notification', ['type' => 'success', 'message' => 'Dil eklendi.']);
    }

    public function update(SaveLanguageRequest $request, Language $language): RedirectResponse
    {
        $language->update($request->safe()->only('is_active'));

        return redirect()->route('admin.settings.index', ['section' => 'languages'])
            ->with('notification', ['type' => 'success', 'message' => 'Dil durumu güncellendi.']);
    }
}
