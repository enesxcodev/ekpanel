<?php

namespace Modules\Users\DataTables;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Modules\Users\Models\User;
use Yajra\DataTables\Facades\DataTables;

class UsersDataTable
{
    public function handle(): JsonResponse
    {
        return DataTables::eloquent(User::query()->select([
            'id', 'name', 'surname', 'email', 'username', 'profile_image', 'is_active',
            'email_verified_at', 'last_login_at', 'last_login_ip', 'last_login_user_agent', 'created_at', 'updated_at',
        ]))
            ->addColumn('full_name', fn (User $user): string => trim($user->name.' '.$user->surname))
            ->filterColumn('full_name', function (Builder $query, string $keyword): void {
                foreach (preg_split('/\s+/u', trim($keyword), flags: PREG_SPLIT_NO_EMPTY) as $term) {
                    $query->where(fn (Builder $names): Builder => $names
                        ->where('name', 'like', '%'.$term.'%')->orWhere('surname', 'like', '%'.$term.'%'));
                }
            })
            ->orderColumn('full_name', fn (Builder $query, string $direction): Builder => $query->orderBy('name', $direction)->orderBy('surname', $direction))
            ->addColumn('actions', fn (User $user): string => view('users::partials.actions', compact('user'))->render())
            ->only(['id', 'full_name', 'email', 'actions'])
            ->rawColumns(['actions'])->toJson();
    }
}
