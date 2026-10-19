<x-admin.table.actions :actions="[
    ['icon' => 'edit', 'label' => 'Düzenle', 'url' => route('admin.products.categories.edit', $category), 'permission' => 'products.categories.update'],
    ['type' => 'form', 'icon' => 'trash', 'label' => 'Sil', 'url' => route('admin.products.categories.destroy', $category), 'permission' => 'products.categories.delete', 'method' => 'DELETE', 'confirm' => 'Bu ürün kategorisini silmek istediğinizden emin misiniz?'],
]" />
