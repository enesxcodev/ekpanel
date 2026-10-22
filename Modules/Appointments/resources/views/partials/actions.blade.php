<x-admin.table.actions :actions="[
    ['icon' => 'edit', 'label' => 'Düzenle', 'url' => route('admin.appointments.edit', $appointment), 'permission' => 'appointments.update'],
    ['type' => 'form', 'icon' => 'trash', 'label' => 'Sil', 'url' => route('admin.appointments.destroy', $appointment), 'permission' => 'appointments.delete', 'method' => 'DELETE', 'confirm' => 'Bu randevuyu silmek istediğinizden emin misiniz?'],
]" />
