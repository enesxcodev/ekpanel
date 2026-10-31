<x-admin.table.actions :actions="[
    ['icon' => 'edit', 'label' => 'Düzenle', 'url' => route('admin.blog.edit', ['blog' => $content->id, 'lang' => request()->attributes->get('blog_language', 'tr')]), 'permission' => 'blog.update'],
    ['type' => 'form', 'icon' => 'trash', 'label' => 'Sil', 'url' => route('admin.blog.destroy', $content), 'permission' => 'blog.delete', 'method' => 'DELETE', 'confirm' => 'Bu içeriği silmek istediğinizden emin misiniz?'],
]" />
