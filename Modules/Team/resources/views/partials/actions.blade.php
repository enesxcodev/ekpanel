<x-admin.table.actions :actions="[
    ['icon' => 'edit', 'label' => 'Düzenle', 'url' => route('admin.team.edit', ['member' => $member->id, 'lang' => $languageCode]), 'permission' => 'team.update'],
    ['type' => 'form', 'icon' => 'trash', 'label' => 'Sil', 'url' => route('admin.team.destroy', $member), 'permission' => 'team.delete', 'method' => 'DELETE', 'confirm' => 'Bu ekip üyesini silmek istediğinizden emin misiniz?'],
]" />
