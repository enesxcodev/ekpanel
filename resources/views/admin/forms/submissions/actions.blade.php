<x-admin.table.actions :actions="[[
    'icon' => 'eye',
    'label' => 'Başvuruyu aç',
    'url' => route('admin.forms.submissions.show', $submission),
]]" />
