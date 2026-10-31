<x-admin.table.actions :actions="[
    ['icon' => 'edit', 'label' => 'Düzenle', 'url' => route('admin.projects.edit', ['project' => $project->id, 'lang' => request()->query('lang', 'tr')]), 'permission' => 'projects.update'],
    ['type' => 'form', 'icon' => 'trash', 'label' => 'Sil', 'url' => route('admin.projects.destroy', $project), 'permission' => 'projects.delete', 'method' => 'DELETE', 'confirm' => 'Projeyi silmek istediğinizden emin misiniz?'],
]" />
