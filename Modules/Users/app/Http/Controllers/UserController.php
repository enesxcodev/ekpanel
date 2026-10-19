<?php

namespace Modules\Users\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\NotifyService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\Users\DataTables\UsersDataTable;
use Modules\Users\Models\User;
use Modules\Users\Services\UserService;

class UserController extends Controller
{
    public function index(): View
    {
        $this->authorize('users.view');

        return view('users::index');
    }

    public function data(UsersDataTable $table): JsonResponse
    {
        $this->authorize('users.view');

        return $table->handle();
    }

    public function edit(User $user): View
    {
        $this->authorize('users.update');

        return view('users::edit', compact('user'));
    }

    public function update(Request $request, User $user, UserService $service, NotifyService $notify): RedirectResponse
    {
        $this->authorize('users.update');
        $user = $service->save($request->only(['name', 'surname', 'email', 'username', 'password', 'profile_image', 'remove_profile_image', 'is_active']), $user);
        $notify->sendToAdminsForActor($request->user('admin'), 'Kullanıcı güncellendi', 'Kullanıcı bilgileri güncellendi.', route('admin.web-users.edit', $user, absolute: false));

        return redirect()->route('admin.web-users.index')->with('notification', ['type' => 'success', 'message' => 'Kullanıcı başarıyla güncellendi.']);
    }

    public function destroy(Request $request, User $user, UserService $service, NotifyService $notify): RedirectResponse
    {
        $this->authorize('users.delete');
        $service->delete($user);
        $notify->sendToAdminsForActor($request->user('admin'), 'Kullanıcı silindi', 'Kullanıcı hesabı silindi.', route('admin.web-users.index', absolute: false));

        return redirect()->route('admin.web-users.index')->with('notification', ['type' => 'success', 'message' => 'Kullanıcı başarıyla silindi.']);
    }
}
