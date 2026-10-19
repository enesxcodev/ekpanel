<?php

namespace App\Http\Controllers;

use App\Models\Forms\SiteForm;
use App\Services\Forms\FormService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PublicFormSubmissionController extends Controller
{
    public function store(Request $request, SiteForm $form, FormService $service): JsonResponse|RedirectResponse
    {
        abort_unless($form->is_active, 404);
        $request->validate(['website' => ['nullable', 'string', 'max:0']]);
        $service->submit($form, $request);
        if ($request->expectsJson()) {
            return response()->json(['message' => $form->success_message], 201);
        }

        return redirect()->back(fallback: route('forms.show', $form->key))->with('form_success.'.$form->id, $form->success_message);
    }
}
