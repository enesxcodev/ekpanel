<x-admin.table.actions :actions="[
    ['icon' => 'edit', 'label' => 'Düzenle', 'url' => route('admin.galeriler.categories.edit', $category), 'permission' => 'galeriler.categories.update'],
    ['type' => 'form', 'icon' => 'trash', 'label' => 'Sil', 'url' => route('admin.galeriler.categories.destroy', $category), 'permission' => 'galeriler.categories.delete', 'method' => 'DELETE', 'confirm' => 'Kategoriyi silmek istediğinizden emin misiniz?'],
]" />
