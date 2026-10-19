<?php

namespace App\Http\Controllers\Admin\Users;

use App\DataTables\Admin\Users\AdminUsersDataTable;
use App\Enums\AdminType\AdminTypeEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Users\StoreAdminRequest;
use App\Http\Requests\Admin\Users\UpdateAdminRequest;
use App\Models\Admin\Admin;
use App\Services\Admin\Users\AdminUserService;
use App\Services\NotifyService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class AdminUserController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Admin::class);

        return view('admin.users.index');
    }

    public function data(AdminUsersDataTable $dataTable): JsonResponse
    {
        $this->authorize('viewAny', Admin::class);

        return $dataTable->handle();
    }

    public function create(): View
    {
        $this->authorize('create', Admin::class);

        return view('admin.users.create', ['types' => AdminTypeEnum::cases()]);
    }

    public function store(StoreAdminRequest $request, AdminUserService $adminUserService, NotifyService $notifications): RedirectResponse
    {
        $admin = $adminUserService->create($request->validated());
        $notifications->sendToAdminsForActor(
            $request->user('admin'),
            'Yönetici eklendi',
            'Yeni bir yönetici hesabı eklendi.',
            route('admin.users.edit', ['admin' => $admin->id], absolute: false),
        );

        return redirect()->route('admin.users.index')->with('notification', [
            'type' => 'success',
            'message' => 'Yönetici hesabı başarıyla eklendi.',
        ]);
    }

    public function edit(Admin $admin): View
    {
        $this->authorize('update', $admin);

        return view('admin.users.edit', [
            'admin' => $admin,
            'types' => AdminTypeEnum::cases(),
        ]);
    }

    public function update(
        UpdateAdminRequest $request,
        Admin $admin,
        AdminUserService $adminUserService,
        NotifyService $notifications,
    ): RedirectResponse {
        $admin = $adminUserService->update($admin, $request->validated(), $request->user('admin'));
        $notifications->sendToAdminsForActor(
            $request->user('admin'),
            'Yönetici güncellendi',
            'Bir yönetici hesabı güncellendi.',
            route('admin.users.edit', ['admin' => $admin->id], absolute: false),
        );

        return redirect()->route('admin.users.index')->with('notification', [
            'type' => 'success',
            'message' => 'Yönetici hesabı başarıyla güncellendi.',
        ]);
    }
}
