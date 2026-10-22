<?php

namespace Modules\Careers\DataTables;

use Illuminate\Http\JsonResponse;
use Modules\Careers\Models\JobPosting;
use Yajra\DataTables\Facades\DataTables;

class JobPostingsDataTable
{
    public function handle(): JsonResponse
    {
        return DataTables::eloquent(JobPosting::query()->select(['id', 'title', 'location', 'employment_type', 'application_deadline', 'is_active']))
            ->editColumn('employment_type', fn (JobPosting $posting): string => match ($posting->employment_type) {
                'part_time' => 'Yarı zamanlı',
                'contract' => 'Sözleşmeli',
                default => 'Tam zamanlı',
            })
            ->editColumn('application_deadline', fn (JobPosting $posting): string => $posting->application_deadline?->format('d.m.Y') ?? '—')
            ->editColumn('is_active', fn (JobPosting $posting): string => $posting->is_active ? 'Aktif' : 'Pasif')
            ->addColumn('actions', fn (JobPosting $posting): string => view('careers::partials.actions', compact('posting'))->render())
            ->rawColumns(['actions'])
            ->toJson();
    }
}
