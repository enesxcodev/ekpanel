<x-admin.table.actions :actions="[
    ['icon' => 'edit', 'label' => 'Düzenle', 'url' => route('admin.testimonials.edit', $testimonial), 'permission' => 'testimonials.update'],
    ['type' => 'form', 'icon' => 'trash', 'label' => 'Sil', 'url' => route('admin.testimonials.destroy', $testimonial), 'permission' => 'testimonials.delete', 'method' => 'DELETE', 'confirm' => 'Bu müşteri yorumunu silmek istediğinizden emin misiniz?'],
]" />
