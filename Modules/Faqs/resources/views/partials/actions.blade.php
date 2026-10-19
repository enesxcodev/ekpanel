<x-admin.table.actions :actions="[
    ['icon' => 'edit', 'label' => 'Düzenle', 'url' => route('admin.faqs.edit', $faq), 'permission' => 'faqs.update'],
    ['type' => 'form', 'icon' => 'trash', 'label' => 'Sil', 'url' => route('admin.faqs.destroy', $faq), 'permission' => 'faqs.delete', 'method' => 'DELETE', 'confirm' => 'Bu soruyu silmek istediğinizden emin misiniz?'],
]" />
