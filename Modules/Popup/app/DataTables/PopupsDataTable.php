<?php

namespace Modules\Popup\DataTables;

use Illuminate\Http\JsonResponse;
use Modules\Popup\Models\Popup;
use Yajra\DataTables\Facades\DataTables;

class PopupsDataTable
{
    public function handle(): JsonResponse
    {
        return DataTables::eloquent(Popup::query()->with('imageMedia')->select(['id', 'title', 'starts_at', 'ends_at', 'repeat_policy', 'repeat_interval', 'repeat_unit', 'is_active', 'image_media_id']))
            ->addColumn('image', fn (Popup $popup): string => view('popup::partials.image', compact('popup'))->render())
            ->editColumn('is_active', fn (Popup $popup): string => $popup->is_active ? 'Aktif' : 'Pasif')
            ->editColumn('starts_at', fn (Popup $popup): ?string => $popup->starts_at?->format('d.m.Y H:i'))
            ->editColumn('ends_at', fn (Popup $popup): ?string => $popup->ends_at?->format('d.m.Y H:i'))
            ->editColumn('repeat_policy', fn (Popup $popup): string => $popup->repeat_policy === 'interval' ? $popup->repeat_interval.' '.($popup->repeat_unit === 'hours' ? 'saat' : 'gün') : 'Kapatıldıktan sonra gösterme')
            ->addColumn('actions', fn (Popup $popup): string => view('popup::partials.actions', compact('popup'))->render())
            ->rawColumns(['image', 'actions'])
            ->toJson();
    }
}
