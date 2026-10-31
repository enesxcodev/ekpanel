<x-admin.table.actions :actions="[
    ['icon' => 'edit', 'label' => 'Düzenle', 'url' => route('admin.documents.edit', ['document' => $document, 'lang' => $languageCode]), 'permission' => 'documents.update'],
    ['type' => 'form', 'icon' => 'trash', 'label' => 'Sil', 'url' => route('admin.documents.destroy', ['document' => $document, 'lang' => $languageCode]), 'permission' => 'documents.delete', 'method' => 'DELETE', 'confirm' => 'Bu belgeyi silmek istediğinizden emin misiniz?'],
]" />
