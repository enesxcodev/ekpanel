<x-admin.table.actions :actions="[
    ['icon' => 'edit', 'label' => 'Düzenle', 'url' => route('admin.documents.categories.edit', $category), 'permission' => 'documents.categories.update'],
    ['type' => 'form', 'icon' => 'trash', 'label' => 'Sil', 'url' => route('admin.documents.categories.destroy', $category), 'permission' => 'documents.categories.delete', 'method' => 'DELETE', 'confirm' => 'Bu kategoriyi silmek istediğinizden emin misiniz? Belgeler kategorisiz kalır.'],
]" />
