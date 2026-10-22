<x-admin.table.actions :actions="[
    ['icon' => 'edit', 'label' => 'Düzenle', 'url' => route('admin.events.edit', $event), 'permission' => 'events.update'],
    ['type' => 'form', 'icon' => 'trash', 'label' => 'Sil', 'url' => route('admin.events.destroy', $event), 'permission' => 'events.delete', 'method' => 'DELETE', 'confirm' => 'Bu etkinliği silmek istediğinizden emin misiniz?'],
]" />
