<?php

namespace App\Http\Controllers;

use App\Models\Forms\SiteForm;
use Illuminate\Contracts\View\View;

class PublicFormController extends Controller
{
    public function show(SiteForm $form): View
    {
        abort_unless($form->is_active, 404);

        return view('forms.show', compact('form'));
    }
}
