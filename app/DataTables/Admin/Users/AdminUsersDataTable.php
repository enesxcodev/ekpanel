<?php

namespace App\DataTables\Admin\Users;

use App\Enums\AdminType\AdminTypeEnum;
use App\Models\Admin\Admin;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\Facades\DataTables;

class AdminUsersDataTable
{
    public function handle(): JsonResponse
    {
        return DataTables::eloquent(Admin::query()->when(request()->user('admin')->type !== AdminTypeEnum::SuperAdmin, fn (Builder $query): Builder => $query->where('type', AdminTypeEnum::Admin->value))->select([
            'id',
            'name',
            'lastname',
            'email',
            'status',
            'type',
            'created_at',
        ]))
            ->addColumn('full_name', fn (Admin $admin): string => view('admin.users.partials.name', compact('admin'))->render())
            ->addColumn('type_label', fn (Admin $admin): string => $admin->type->label())
            ->addColumn('status_badge', function (Admin $admin): string {
                $class = $admin->status ? 'bg-success-lt text-success' : 'bg-secondary-lt text-secondary';
                $label = $admin->status ? 'Aktif' : 'Pasif';

                return '<span class="badge '.$class.'">'.$label.'</span>';
            })
            ->editColumn('created_at', fn (Admin $admin): string => $admin->created_at->format('d.m.Y'))
            ->addColumn('actions', fn (Admin $admin): string => view('admin.users.partials.actions', compact('admin'))->render())
            ->filterColumn('full_name', function (Builder $query, string $keyword): void {
                $query->where(function (Builder $query) use ($keyword): void {
                    $query->where('name', 'like', '%'.$keyword.'%')
                        ->orWhere('lastname', 'like', '%'.$keyword.'%')
                        ->orWhere('email', 'like', '%'.$keyword.'%');
                });
            })
            ->filterColumn('type_label', function (Builder $query, string $keyword): void {
                $matchingTypes = collect(AdminTypeEnum::cases())
                    ->filter(fn (AdminTypeEnum $type): bool => str_contains(mb_strtolower($type->label()), mb_strtolower($keyword)))
                    ->map(fn (AdminTypeEnum $type): int => $type->value)
                    ->all();

                $query->whereIn('type', $matchingTypes ?: [-1]);
            })
            ->filter(function (Builder $query): void {
                $status = request()->input('status');

                if (in_array($status, ['0', '1'], true)) {
                    $query->where('status', (int) $status);
                }
            }, true)
            ->orderColumn('full_name', 'name $1')
            ->rawColumns(['full_name', 'status_badge', 'actions'])
            ->toJson();
    }
}
