<x-admin.table.actions :actions="[[
    'icon' => 'edit',
    'label' => 'Düzenle',
    'url' => route('admin.users.edit', $admin),
    'permission' => 'update',
    'model' => $admin,
]]" />
