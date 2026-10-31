<x-admin.table.actions :actions="[
    ['icon' => 'edit', 'label' => 'Düzenle', 'url' => route('admin.services.edit', ['service' => $service->id, 'lang' => request()->attributes->get('services_language', 'tr')]), 'permission' => 'services.update'],
    ['type' => 'form', 'icon' => 'trash', 'label' => 'Sil', 'url' => route('admin.services.destroy', $service), 'permission' => 'services.delete', 'method' => 'DELETE', 'confirm' => 'Bu hizmeti silmek istediğinizden emin misiniz?'],
]" />
