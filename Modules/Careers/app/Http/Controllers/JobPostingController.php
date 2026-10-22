<?php

namespace Modules\Careers\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\NotifyService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Modules\Careers\DataTables\JobPostingsDataTable;
use Modules\Careers\Http\Requests\SaveJobPostingRequest;
use Modules\Careers\Models\JobPosting;
use Modules\Careers\Services\CareerService;

class JobPostingController extends Controller
{
    public function index(): View
    {
        $this->authorize('careers.view');

        return view('careers::index', ['postings' => JobPosting::query()->latest()->paginate(20)]);
    }

    public function create(): View
    {
        $this->authorize('careers.create');

        return view('careers::create', ['posting' => null]);
    }

    public function data(JobPostingsDataTable $dataTable): JsonResponse
    {
        $this->authorize('careers.view');

        return $dataTable->handle();
    }

    public function store(SaveJobPostingRequest $request, CareerService $service, NotifyService $notifications): RedirectResponse
    {
        $this->authorize('careers.create');
        $posting = $service->save($request->validated());
        $notifications->sendToAdminsForActor($request->user('admin'), 'İş ilanı eklendi', 'Yeni bir iş ilanı eklendi.', route('admin.careers.edit', $posting, false));

        return redirect()->route('admin.careers.index')->with('notification', ['type' => 'success', 'message' => 'İş ilanı başarıyla eklendi.']);
    }

    public function edit(JobPosting $career): View
    {
        $this->authorize('careers.update');

        return view('careers::edit', ['posting' => $career]);
    }

    public function update(SaveJobPostingRequest $request, JobPosting $career, CareerService $service, NotifyService $notifications): RedirectResponse
    {
        $this->authorize('careers.update');
        $posting = $service->save($request->validated(), $career);
        $notifications->sendToAdminsForActor($request->user('admin'), 'İş ilanı güncellendi', 'Bir iş ilanı güncellendi.', route('admin.careers.edit', $posting, false));

        return redirect()->route('admin.careers.index')->with('notification', ['type' => 'success', 'message' => 'İş ilanı başarıyla güncellendi.']);
    }

    public function destroy(JobPosting $career, CareerService $service, NotifyService $notifications): RedirectResponse
    {
        $this->authorize('careers.delete');
        $service->delete($career);
        $notifications->sendToAdminsForActor(request()->user('admin'), 'İş ilanı silindi', 'Bir iş ilanı silindi.', route('admin.careers.index', [], false));

        return redirect()->route('admin.careers.index')->with('notification', ['type' => 'success', 'message' => 'İş ilanı başarıyla silindi.']);
    }
}
