<x-admin.table.actions :actions="[
    ['icon' => 'edit', 'label' => 'Düzenle', 'url' => route('admin.products.edit', $product), 'permission' => 'products.update'],
    ['type' => 'form', 'icon' => 'trash', 'label' => 'Sil', 'url' => route('admin.products.destroy', $product), 'permission' => 'products.delete', 'method' => 'DELETE', 'confirm' => 'Bu ürüni silmek istediğinizden emin misiniz?'],
]" />
