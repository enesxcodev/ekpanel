<x-admin.table.actions :actions="[
    ['icon' => 'edit', 'label' => 'Düzenle', 'url' => route('admin.popup.edit', $popup), 'permission' => 'popup.update'],
    ['type' => 'form', 'icon' => 'trash', 'label' => 'Sil', 'url' => route('admin.popup.destroy', $popup), 'permission' => 'popup.delete', 'method' => 'DELETE', 'confirm' => 'Bu popup kaydını silmek istediğinizden emin misiniz?'],
]" />
