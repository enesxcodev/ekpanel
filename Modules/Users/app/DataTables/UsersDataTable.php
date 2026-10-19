<?php

namespace Modules\Users\DataTables;

use Illuminate\Http\JsonResponse;
use Modules\Users\Models\User;
use Yajra\DataTables\Facades\DataTables;

class UsersDataTable
{
    public function handle(): JsonResponse
    {
        return DataTables::eloquent(User::query()->select([
            'id', 'name', 'surname', 'email', 'username', 'profile_image', 'is_active',
            'email_verified_at', 'last_login_at', 'last_login_ip', 'last_login_user_agent', 'created_at',
        ]))
            ->addColumn('photo', fn (User $user): string => view('users::partials.photo', compact('user'))->render())
            ->editColumn('is_active', fn (User $user): string => view('users::partials.status', compact('user'))->render())
            ->editColumn('email_verified_at', fn (User $user): string => $user->hasVerifiedEmail() ? 'Doğrulandı' : 'Doğrulanmadı')
            ->editColumn('last_login_at', fn (User $user): ?string => $user->last_login_at?->format('d.m.Y H:i'))
            ->editColumn('created_at', fn (User $user): ?string => $user->created_at?->format('d.m.Y H:i'))
            ->addColumn('actions', fn (User $user): string => view('users::partials.actions', compact('user'))->render())
            ->rawColumns(['photo', 'is_active', 'actions'])->toJson();
    }
}
