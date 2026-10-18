<x-admin.layout title="Galeriler kategorileri">
    <div class="card">
        <div class="card-header"><h2 class="card-title me-auto">Galeriler kategorileri</h2>
            @can('galeriler.categories.create')<a href="{{ route('admin.galeriler.categories.create') }}" class="btn btn-primary">Kategori ekle</a>@endcan
        </div>
        <div class="card-body">
            @if ($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
            @if ($categories->isEmpty())<p class="text-secondary">Henüz kategori bulunmuyor.</p>
            @else @include('galeriler::categories.partials.tree', ['parentId' => null, 'byParent' => $categories->groupBy(fn ($entry) => $entry->parent_id ?? 0)]) @endif
        </div>
    </div>
</x-admin.layout>