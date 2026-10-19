<x-admin.table.actions :actions="[
    ['icon' => 'edit', 'label' => 'Düzenle', 'url' => route('admin.slider.edit', $slider), 'permission' => 'slider.update'],
    ['type' => 'form', 'icon' => 'trash', 'label' => 'Sil', 'url' => route('admin.slider.destroy', $slider), 'permission' => 'slider.delete', 'method' => 'DELETE', 'confirm' => 'Bu slider kaydını silmek istediğinizden emin misiniz?'],
]" />
