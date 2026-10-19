<x-admin.table.actions :actions="[
    ['icon' => 'edit', 'label' => 'Düzenle', 'url' => route('admin.menus.edit', $menu), 'permission' => 'menus.update'],
    ['type' => 'form', 'icon' => 'trash', 'label' => 'Sil', 'url' => route('admin.menus.destroy', $menu), 'permission' => 'menus.delete', 'method' => 'DELETE', 'confirm' => 'Bu menüyü ve tüm öğelerini silmek istediğinizden emin misiniz?'],
]" />
