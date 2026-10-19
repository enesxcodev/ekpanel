<?php

namespace App\Http\Controllers\Admin\Forms;

use App\Http\Controllers\Controller;
use App\Models\Forms\FormSubmission;
use App\Models\Forms\SiteForm;
use App\Services\Forms\FormService;
use App\Services\NotifyService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Yajra\DataTables\Facades\DataTables;

class FormSubmissionController extends Controller
{
    public function index(): View
    {
        $this->authorize('forms.submissions.view');

        return view('admin.forms.submissions.index', ['forms' => SiteForm::query()->orderBy('name')->get(['id', 'name'])]);
    }

    public function data(Request $request): JsonResponse
    {
        $this->authorize('forms.submissions.view');
        $filters = $request->validate(['form_id' => ['nullable', 'integer', 'exists:site_forms,id'], 'status' => ['nullable', Rule::in(['new', 'read', 'in_progress'])]]);
        $query = FormSubmission::query()->select(['id', 'site_form_id', 'form_name', 'name', 'email', 'phone', 'status', 'created_at']);
        if (! empty($filters['form_id'])) {
            $query->where('site_form_id', $filters['form_id']);
        }
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return DataTables::eloquent($query)
            ->editColumn('status', fn (FormSubmission $submission): string => ['new' => 'Yeni', 'read' => 'Okundu', 'in_progress' => 'İşleme alındı'][$submission->status])
            ->editColumn('created_at', fn (FormSubmission $submission): string => $submission->created_at->format('d.m.Y H:i'))
            ->addColumn('actions', fn (FormSubmission $submission): string => view('admin.forms.submissions.actions', compact('submission'))->render())
            ->rawColumns(['actions'])->toJson();
    }

    public function show(FormSubmission $submission): View
    {
        $this->authorize('forms.submissions.view');

        return view('admin.forms.submissions.show', compact('submission'));
    }

    public function update(Request $request, FormSubmission $submission, NotifyService $notifications): RedirectResponse
    {
        $this->authorize('forms.submissions.update');
        $submission->update($request->validate(['status' => ['required', Rule::in(['new', 'read', 'in_progress'])]]));
        $notifications->sendToAdminsForActor(
            $request->user('admin'),
            'Form başvurusu güncellendi',
            'Bir form başvurusunun durumu güncellendi.',
            route('admin.forms.submissions.show', ['submission' => $submission->id], absolute: false),
        );

        return back()->with('notification', ['type' => 'success', 'message' => 'Başvuru durumu güncellendi.']);
    }

    public function destroy(FormSubmission $submission, FormService $service, Request $request, NotifyService $notifications): RedirectResponse
    {
        $this->authorize('forms.submissions.delete');
        $service->deleteSubmission($submission);
        $notifications->sendToAdminsForActor(
            $request->user('admin'),
            'Form başvurusu silindi',
            'Bir form başvurusu silindi.',
            route('admin.forms.submissions.index', absolute: false),
        );

        return redirect()->route('admin.forms.submissions.index')->with('notification', ['type' => 'success', 'message' => 'Başvuru silindi.']);
    }

    public function download(FormSubmission $submission, string $field): StreamedResponse
    {
        $this->authorize('forms.submissions.view');
        $answer = $submission->answers[$field] ?? null;
        abort_unless(is_array($answer) && $answer['type'] === 'file' && is_array($answer['value']), 404);
        $file = $answer['value'];
        abort_unless(str_starts_with($file['path'] ?? '', 'form-submissions/') && ! str_contains($file['path'], '..'), 404);
        abort_unless(Storage::disk('local')->exists($file['path']), 404);

        return Storage::disk('local')->download($file['path'], $file['name'], ['X-Content-Type-Options' => 'nosniff']);
    }
}
