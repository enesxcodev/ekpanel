<x-admin.table.actions :actions="[
    ['icon' => 'edit', 'label' => 'Düzenle', 'url' => route('admin.pages.categories.edit', ['category' => $category->id, 'lang' => request()->query('lang', 'tr')]), 'permission' => 'pages.categories.update'],
    ['type' => 'form', 'icon' => 'trash', 'label' => 'Sil', 'url' => route('admin.pages.categories.destroy', $category), 'permission' => 'pages.categories.delete', 'method' => 'DELETE', 'confirm' => 'Bu sayfa kategorisini silmek istediğinizden emin misiniz? İçinde sayfa veya alt kategori bulunmamalıdır.'],
]" />
