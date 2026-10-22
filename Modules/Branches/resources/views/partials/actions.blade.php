<x-admin.table.actions :actions="[
    ['icon' => 'edit', 'label' => 'Düzenle', 'url' => route('admin.branches.edit', $branch), 'permission' => 'branches.update'],
    ['type' => 'form', 'icon' => 'trash', 'label' => 'Sil', 'url' => route('admin.branches.destroy', $branch), 'permission' => 'branches.delete', 'method' => 'DELETE', 'confirm' => 'Bu şube / bayi kaydını silmek istediğinizden emin misiniz?'],
]" />
