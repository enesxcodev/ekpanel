<x-admin.table.actions :actions="[
    ['icon' => 'edit', 'label' => 'Düzenle', 'url' => route('admin.projects.categories.edit', $category), 'permission' => 'projects.categories.update'],
    ['type' => 'form', 'icon' => 'trash', 'label' => 'Sil', 'url' => route('admin.projects.categories.destroy', $category), 'permission' => 'projects.categories.delete', 'method' => 'DELETE', 'confirm' => 'Kategoriyi silmek istediğinizden emin misiniz?'],
]" />
