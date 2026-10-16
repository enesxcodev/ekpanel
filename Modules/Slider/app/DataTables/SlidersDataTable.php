<?php

namespace Modules\Slider\DataTables;

use Illuminate\Http\JsonResponse;
use Modules\Slider\Models\Slider;
use Yajra\DataTables\Facades\DataTables;

class SlidersDataTable
{
    public function handle(): JsonResponse
    {
        return DataTables::eloquent(Slider::query()->select(['id', 'title', 'subtitle']))
            ->addColumn('actions', fn (Slider $slider): string => view('slider::partials.actions', compact('slider'))->render())
            ->rawColumns(['actions'])
            ->toJson();
    }
}
