<?php

namespace App\Http\Controllers\Admin\Roles;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Roles\UpdateRolePermissionsRequest;
use App\Models\Settings\SettingSection;
use App\Services\Admin\Modules\ModulePanelService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AdminRoleController extends Controller
{
    public function index(): View
    {
        $this->authorize('manage-admin-roles');

        return view('admin.roles.index', ['roles' => Role::query()->where('guard_name', 'admin')->withCount('permissions')->orderBy('name')->get()]);
    }

    public function edit(Role $role, ModulePanelService $modulePanel): View
    {
        $this->authorize('manage-admin-roles');
        abort_unless($role->guard_name === 'admin', 404);

        return view('admin.roles.edit', [
            'role' => $role,
            'groups' => Permission::query()->where('guard_name', 'admin')->orderBy('name')->get()->groupBy(fn (Permission $permission): string => str($permission->name)->beforeLast('.')->toString()),
            'selected' => $role->permissions->pluck('name')->all(),
            'permissionLabels' => $modulePanel->permissionLabels() + ['settings' => 'Ayarlar', 'media' => 'Medya Kütüphanesi', 'forms' => 'Form yönetimi', 'forms.submissions' => 'Gelen başvurular'] + SettingSection::query()->get()
                ->mapWithKeys(fn (SettingSection $section): array => ['settings.sections.'.$section->key => 'Ayarlar / '.$section->label])->all(),
            'actions' => ['menu' => 'Menü Show', 'view' => 'Listele', 'show' => 'Görüntüle', 'create' => 'Oluştur', 'update' => 'Güncelle', 'delete' => 'Sil'],
        ]);
    }

    public function update(UpdateRolePermissionsRequest $request, Role $role): RedirectResponse
    {
        abort_unless($role->guard_name === 'admin', 404);
        abort_if($role->name === 'super-admin', 403, 'Süper adminin sınırsız erişimi değiştirilemez.');
        $role->syncPermissions($request->validated('permissions', []));

        return redirect()->route('admin.roles.edit', $role)->with('notification', ['type' => 'success', 'message' => 'Rol izinleri başarıyla güncellendi.']);
    }
}
