<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Models\Settings\SettingSection;
use Illuminate\Contracts\View\View;

class SettingConfigurationController extends Controller
{
    public function index(): View
    {
        $this->authorize('manage-setting-definitions');

        return view('admin.settings.configuration.index', [
            'sections' => SettingSection::query()->with('definitions')->orderBy('sort_order')->orderBy('id')->get(),
        ]);
    }
}
