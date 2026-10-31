<x-admin.table.actions :actions="[
    ['icon' => 'edit', 'label' => 'Düzenle', 'url' => route('admin.faqs.edit', array_filter(['faq' => $faq->id, 'lang' => $langCode])), 'permission' => 'faqs.update'],
    ['type' => 'form', 'icon' => 'trash', 'label' => 'Sil', 'url' => route('admin.faqs.destroy', array_filter(['faq' => $faq->id, 'lang' => $langCode])), 'permission' => 'faqs.delete', 'method' => 'DELETE', 'confirm' => 'Bu soruyu silmek istediğinizden emin misiniz?'],
]" />
