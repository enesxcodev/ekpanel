@php($statuses = ['draft' => ['Taslak', 'secondary'], 'published' => ['Yayında', 'success'], 'archived' => ['Arşiv', 'warning']])
<span class="badge bg-{{ $statuses[$content->status][1] }}-lt">{{ $statuses[$content->status][0] }}</span>
