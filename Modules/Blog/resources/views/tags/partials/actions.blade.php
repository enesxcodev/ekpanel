<x-admin.table.actions :actions="[
    ['icon' => 'edit', 'label' => 'Düzenle', 'url' => route('admin.blog.tags.edit', $tag), 'permission' => 'blog.tags.update'],
    ['type' => 'form', 'icon' => 'trash', 'label' => 'Sil', 'url' => route('admin.blog.tags.destroy', $tag), 'permission' => 'blog.tags.delete', 'method' => 'DELETE', 'confirm' => 'Etiket silinir; içerik kayıtları korunur. Devam edilsin mi?'],
]" />
