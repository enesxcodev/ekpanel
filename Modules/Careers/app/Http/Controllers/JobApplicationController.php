<?php

namespace Modules\Careers\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Modules\Careers\Http\Requests\SaveJobApplicationRequest;
use Modules\Careers\Models\JobApplication;
use Modules\Careers\Models\JobPosting;

class JobApplicationController extends Controller
{
    public function index(): View
    {
        $this->authorize('careers.view');

        return view('careers::applications.index', ['applications' => JobApplication::query()->with('posting:id,title')->latest()->paginate(20)]);
    }

    public function create(): View
    {
        $this->authorize('careers.create');

        return view('careers::applications.create', ['postings' => JobPosting::query()->orderBy('title')->get(['id', 'title'])]);
    }

    public function store(SaveJobApplicationRequest $request): RedirectResponse
    {
        $this->authorize('careers.create');
        JobApplication::query()->create($request->validated());

        return redirect()->route('admin.careers.applications.index')->with('notification', ['type' => 'success', 'message' => 'Aday başvurusu eklendi.']);
    }
}
