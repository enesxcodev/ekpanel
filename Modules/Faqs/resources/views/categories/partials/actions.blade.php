<x-admin.table.actions :actions="[
    ['icon' => 'edit', 'label' => 'Düzenle', 'url' => route('admin.faqs.categories.edit', $category), 'permission' => 'faqs.categories.update'],
    ['type' => 'form', 'icon' => 'trash', 'label' => 'Sil', 'url' => route('admin.faqs.categories.destroy', $category), 'permission' => 'faqs.categories.delete', 'method' => 'DELETE', 'confirm' => 'Bu kategoriyi silmek istediğinizden emin misiniz? Sorular kategorisiz kalır.'],
]" />
