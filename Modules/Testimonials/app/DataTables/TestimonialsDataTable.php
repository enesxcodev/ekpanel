<?php

namespace Modules\Testimonials\DataTables;

use App\Models\Settings\Language;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Modules\Testimonials\Models\Testimonial;
use Yajra\DataTables\Facades\DataTables;

class TestimonialsDataTable
{
    public function handle(?Language $language = null): JsonResponse
    {
        $query = Testimonial::query()->with(['imageMedia', 'translations' => fn ($query) => $query->where('lang_id', $language?->id)])->select(['id', 'image', 'image_media_id', 'is_active', 'sort_order', 'created_at']);

        return DataTables::eloquent($query)
            ->addColumn('customer_name', fn (Testimonial $testimonial): string => $testimonial->translations->first()?->customer_name ?? 'Çeviri yok')
            ->addColumn('company', fn (Testimonial $testimonial): string => $testimonial->translations->first()?->company ?? '')
            ->addColumn('job_title', fn (Testimonial $testimonial): string => $testimonial->translations->first()?->job_title ?? '')
            ->addColumn('content', fn (Testimonial $testimonial): string => Str::limit(strip_tags($testimonial->translations->first()?->content ?? 'Çeviri yok'), 180))
            ->addColumn('photo', fn (Testimonial $testimonial): string => view('testimonials::partials.photo', compact('testimonial'))->render())
            ->editColumn('is_active', fn (Testimonial $testimonial): string => view('testimonials::partials.status', compact('testimonial'))->render())
            ->editColumn('created_at', fn (Testimonial $testimonial): ?string => $testimonial->created_at?->format('d.m.Y H:i'))
            ->addColumn('actions', fn (Testimonial $testimonial): string => view('testimonials::partials.actions', compact('testimonial'))->render())
            ->rawColumns(['photo', 'is_active', 'actions'])
            ->toJson();
    }
}
