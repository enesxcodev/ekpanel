<?php

namespace App\View\Components;

use App\Models\Forms\SiteForm as FormModel;
use App\Services\Forms\FormContext;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class SiteForm extends Component
{
    public FormModel $form;

    public string $themeView;

    public function __construct(string $key, string $variant = 'default', public bool $unstyled = false, public ?string $context = null)
    {
        $this->form = FormModel::query()->where('key', $key)->where('is_active', true)->with('fields')->firstOrFail();
        app(FormContext::class)->resolve($context, $this->form);
        abort_unless(preg_match('/^[a-z0-9_-]+$/', $variant), 404);
        $candidate = 'forms.themes.'.$variant;
        $this->themeView = view()->exists($candidate) ? $candidate : 'forms.themes.default';
    }

    public function render(): View
    {
        return view('components.site-form');
    }
}
