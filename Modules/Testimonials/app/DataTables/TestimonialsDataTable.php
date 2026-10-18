<?php

namespace Modules\Testimonials\DataTables;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Modules\Testimonials\Models\Testimonial;
use Yajra\DataTables\Facades\DataTables;

class TestimonialsDataTable
{
    public function handle(): JsonResponse
    {
        return DataTables::eloquent(Testimonial::query()->with('imageMedia')->select(['id', 'customer_name', 'company', 'job_title', 'content', 'image', 'image_media_id', 'is_active', 'sort_order', 'created_at']))
            ->addColumn('photo', fn (Testimonial $testimonial): string => view('testimonials::partials.photo', compact('testimonial'))->render())
            ->editColumn('content', fn (Testimonial $testimonial): string => Str::limit(strip_tags($testimonial->content), 180))
            ->editColumn('is_active', fn (Testimonial $testimonial): string => view('testimonials::partials.status', compact('testimonial'))->render())
            ->editColumn('created_at', fn (Testimonial $testimonial): ?string => $testimonial->created_at?->format('d.m.Y H:i'))
            ->addColumn('actions', fn (Testimonial $testimonial): string => view('testimonials::partials.actions', compact('testimonial'))->render())
            ->rawColumns(['photo', 'is_active', 'actions'])
            ->toJson();
    }
}
