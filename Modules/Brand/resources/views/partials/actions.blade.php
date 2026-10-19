<x-admin.table.actions :actions="[
    ['icon' => 'edit', 'label' => 'Düzenle', 'url' => route('admin.brand.edit', $brand), 'permission' => 'brand.update'],
    ['type' => 'form', 'icon' => 'trash', 'label' => 'Sil', 'url' => route('admin.brand.destroy', $brand), 'permission' => 'brand.delete', 'method' => 'DELETE', 'confirm' => 'Bu markayı silmek istediğinizden emin misiniz?'],
]" />
