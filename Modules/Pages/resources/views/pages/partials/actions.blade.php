<x-admin.table.actions :actions="[
    ['icon' => 'edit', 'label' => 'Düzenle', 'url' => route('admin.pages.edit', ['page' => $page->id, 'lang' => request()->query('lang', 'tr')]), 'permission' => 'pages.update'],
    ['type' => 'form', 'icon' => 'trash', 'label' => 'Sil', 'url' => route('admin.pages.destroy', $page), 'permission' => 'pages.delete', 'method' => 'DELETE', 'confirmModal' => ['title' => 'Sayfayı sil', 'message' => 'Bu sayfayı silmek istediğinizden emin misiniz?', 'confirmLabel' => 'Sil', 'cancelLabel' => 'Vazgeç', 'confirmClass' => 'btn-danger']],
]" />
