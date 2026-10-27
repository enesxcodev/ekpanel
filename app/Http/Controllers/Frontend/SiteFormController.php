<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Forms\SiteForm;
use App\Services\Forms\FormService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SiteFormController extends Controller
{
    public function __construct(private FormService $forms) {}

    public function __invoke(Request $request, SiteForm $form): RedirectResponse
    {
        abort_unless($form->is_active && $form->fields()->exists(), 404);
        if (filled($request->input('website'))) {
            return redirect()->route('home', [], 303);
        }
        $this->forms->submit($form, $request);

        return redirect(route('home').'#iletisim-formu', 303)
            ->with('form_success.'.$form->id, $form->success_message);
    }
}
