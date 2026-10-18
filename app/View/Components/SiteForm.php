<?php

namespace App\View\Components;

use App\Models\Forms\SiteForm as FormModel;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class SiteForm extends Component
{
    public FormModel $form;

    public string $themeView;

    public function __construct(string $key, string $variant = 'default', public bool $unstyled = false)
    {
        $this->form = FormModel::query()->where('key', $key)->where('is_active', true)->with('fields')->firstOrFail();
        abort_unless(preg_match('/^[a-z0-9_-]+$/', $variant), 404);
        $candidate = 'forms.themes.'.$variant;
        $this->themeView = view()->exists($candidate) ? $candidate : 'forms.themes.default';
    }

    public function render(): View
    {
        return view('components.site-form');
    }
}
