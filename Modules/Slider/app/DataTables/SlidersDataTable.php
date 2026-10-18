<?php

namespace Modules\Slider\DataTables;

use Illuminate\Http\JsonResponse;
use Modules\Slider\Models\Slider;
use Yajra\DataTables\Facades\DataTables;

class SlidersDataTable
{
    public function handle(): JsonResponse
    {
        return DataTables::eloquent(Slider::query()->with('imageMedia')->select(['id', 'title', 'subtitle', 'is_active', 'image', 'image_media_id']))
            ->addColumn('image', fn (Slider $slider): string => view('slider::partials.image', compact('slider'))->render())
            ->editColumn('is_active', fn (Slider $slider): string => $slider->is_active ? 'Aktif' : 'Pasif')
            ->addColumn('actions', fn (Slider $slider): string => view('slider::partials.actions', compact('slider'))->render())
            ->rawColumns(['image', 'actions'])
            ->toJson();
    }
}
