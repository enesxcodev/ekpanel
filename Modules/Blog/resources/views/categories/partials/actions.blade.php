<x-admin.table.actions :actions="[
    ['icon' => 'edit', 'label' => 'Düzenle', 'url' => route('admin.blog.categories.edit', ['category' => $category->id, 'lang' => request()->attributes->get('blog_language', 'tr')]), 'permission' => 'blog.categories.update'],
    ['type' => 'form', 'icon' => 'trash', 'label' => 'Sil', 'url' => route('admin.blog.categories.destroy', $category), 'permission' => 'blog.categories.delete', 'method' => 'DELETE', 'confirm' => 'İçerik veya alt kategori varsa kategori silinemez. Devam edilsin mi?'],
]" />
