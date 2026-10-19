<x-admin.table.actions :actions="[
    [
        'type' => 'modal',
        'icon' => 'eye',
        'label' => 'Görüntüle',
        'target' => '#user-details-'.$user->id,
    ],
    [
        'icon' => 'edit',
        'label' => 'Düzenle',
        'url' => route('admin.web-users.edit', $user),
        'permission' => 'users.update',
    ],
    [
        'type' => 'form',
        'icon' => 'trash',
        'label' => 'Sil',
        'url' => route('admin.web-users.destroy', $user),
        'permission' => 'users.delete',
        'method' => 'DELETE',
        'confirm' => 'Bu kullanıcıyı silmek istediğinizden emin misiniz?',
    ],
]" />
@include('users::partials.details')
