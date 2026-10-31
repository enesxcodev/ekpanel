<?php

namespace Modules\Slider\DataTables;

use Illuminate\Http\JsonResponse;
use Modules\Slider\Models\Slider;
use Modules\Slider\Services\SliderLanguageService;
use Yajra\DataTables\Facades\DataTables;

class SlidersDataTable
{
    public function handle(SliderLanguageService $languages): JsonResponse
    {
        $language = $languages->forAdmin(request()->query('lang'));

        return DataTables::eloquent(Slider::query()->with(['imageMedia', 'translations' => fn ($query) => $query->where('lang_id', $language?->id)])->select(['id', 'is_active', 'image', 'image_media_id']))
            ->addColumn('title', function (Slider $slider): string {
                $translation = $slider->translations->first();
                $slider->setRelation('selectedTranslation', $translation);

                return $translation?->title ?? '';
            })
            ->addColumn('subtitle', function (Slider $slider): string {
                $translation = $slider->translations->first();
                $slider->setRelation('selectedTranslation', $translation);

                return $translation?->subtitle ?? '';
            })
            ->addColumn('image', fn (Slider $slider): string => view('slider::partials.image', compact('slider'))->render())
            ->editColumn('is_active', fn (Slider $slider): string => $slider->is_active ? 'Aktif' : 'Pasif')
            ->addColumn('actions', fn (Slider $slider): string => view('slider::partials.actions', compact('slider'))->render())
            ->rawColumns(['image', 'actions'])
            ->toJson();
    }
}
