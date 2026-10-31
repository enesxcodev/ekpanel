<x-admin.table.actions :actions="[
    ['icon' => 'edit', 'label' => 'Düzenle', 'url' => route('admin.sector.edit', ['sector' => $sector, 'lang' => request()->query('lang', 'tr')]), 'permission' => 'sector.update'],
    ['type' => 'form', 'icon' => 'trash', 'label' => 'Sil', 'url' => route('admin.sector.destroy', $sector), 'permission' => 'sector.delete', 'method' => 'DELETE', 'confirm' => 'Bu sektörü silmek istediğinizden emin misiniz?'],
]" />
