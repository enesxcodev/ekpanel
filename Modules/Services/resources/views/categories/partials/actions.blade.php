<x-admin.table.actions :actions="[
    ['icon' => 'edit', 'label' => 'Düzenle', 'url' => route('admin.services.categories.edit', $category), 'permission' => 'services.categories.update'],
    ['type' => 'form', 'icon' => 'trash', 'label' => 'Sil', 'url' => route('admin.services.categories.destroy', $category), 'permission' => 'services.categories.delete', 'method' => 'DELETE', 'confirm' => 'Bu hizmet kategorisini silmek istediğinizden emin misiniz?'],
]" />
