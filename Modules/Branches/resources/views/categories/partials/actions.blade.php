<x-admin.table.actions :actions="[
    ['icon' => 'edit', 'label' => 'Düzenle', 'url' => route('admin.branches.categories.edit', $category), 'permission' => 'branches.categories.update'],
    ['type' => 'form', 'icon' => 'trash', 'label' => 'Sil', 'url' => route('admin.branches.categories.destroy', $category), 'permission' => 'branches.categories.delete', 'method' => 'DELETE', 'confirm' => 'Bu kategoriyi silmek istediğinizden emin misiniz?'],
]" />
