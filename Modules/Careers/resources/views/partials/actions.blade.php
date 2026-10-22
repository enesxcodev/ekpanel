<x-admin.table.actions :actions="[
    ['icon' => 'edit', 'label' => 'Düzenle', 'url' => route('admin.careers.edit', $posting), 'permission' => 'careers.update'],
    ['type' => 'form', 'icon' => 'trash', 'label' => 'Sil', 'url' => route('admin.careers.destroy', $posting), 'permission' => 'careers.delete', 'method' => 'DELETE', 'confirm' => 'Bu iş ilanını silmek istediğinizden emin misiniz?'],
]" />
