<x-admin.table.actions :actions="[
    ['icon' => 'edit', 'label' => 'Düzenle', 'url' => route('admin.forms.edit', $form), 'permission' => 'forms.update'],
    ['icon' => 'list', 'label' => 'Başvurular', 'url' => route('admin.forms.submissions.index', ['form_id' => $form->id]), 'permission' => 'forms.submissions.view'],
    ['type' => 'form', 'icon' => 'trash', 'label' => 'Sil', 'url' => route('admin.forms.destroy', $form), 'permission' => 'forms.delete', 'method' => 'DELETE', 'confirm' => 'Bu formu silmek istediğinizden emin misiniz?'],
]" />
