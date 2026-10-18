<?php

namespace App\Http\Controllers\Admin\Forms;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Forms\SaveFormRequest;
use App\Models\Forms\SiteForm;
use App\Services\Forms\FormSchema;
use App\Services\Forms\FormService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;

class FormController extends Controller
{
    public function index(): View
    {
        $this->authorize('forms.view');

        return view('admin.forms.index');
    }

    public function data(): JsonResponse
    {
        $this->authorize('forms.view');

        return DataTables::eloquent(SiteForm::query()->select(['site_forms.id', 'name', 'key', 'is_active'])->withCount(['fields', 'submissions']))
            ->editColumn('is_active', fn (SiteForm $form): string => $form->is_active ? 'Aktif' : 'Pasif')
            ->addColumn('actions', fn (SiteForm $form): string => view('admin.forms.partials.actions', compact('form'))->render())
            ->rawColumns(['actions'])->toJson();
    }

    public function create(): View
    {
        $this->authorize('forms.create');

        return view('admin.forms.editor', ['form' => null, 'editorFields' => []]);
    }

    public function store(SaveFormRequest $request, FormService $service): RedirectResponse
    {
        $service->save($request->validated());

        return redirect()->route('admin.forms.index')->with('notification', ['type' => 'success', 'message' => 'Form oluşturuldu.']);
    }

    public function edit(SiteForm $form, FormSchema $schema): View
    {
        $this->authorize('forms.update');

        return view('admin.forms.editor', ['form' => $form, 'editorFields' => $schema->editorFields($form)]);
    }

    public function update(SaveFormRequest $request, SiteForm $form, FormService $service): RedirectResponse
    {
        $service->save($request->validated(), $form);

        return redirect()->route('admin.forms.index')->with('notification', ['type' => 'success', 'message' => 'Form güncellendi.']);
    }

    public function destroy(SiteForm $form): RedirectResponse
    {
        $this->authorize('forms.delete');
        DB::transaction(function () use ($form): void {
            $form = SiteForm::query()->lockForUpdate()->findOrFail($form->id);
            if ($form->submissions()->exists()) {
                throw ValidationException::withMessages(['form' => 'Başvurusu olan form silinemez. Formu pasif yapabilirsiniz.']);
            }
            $form->delete();
        });

        return redirect()->route('admin.forms.index')->with('notification', ['type' => 'success', 'message' => 'Form silindi.']);
    }

    public function import(Request $request, FormSchema $schema): JsonResponse
    {
        abort_unless($request->user('admin')->canAny(['forms.create', 'forms.update']), 403);
        $data = $request->validate(['html' => ['required', 'string', 'max:100000']]);

        return response()->json($schema->import($data['html']));
    }
}
