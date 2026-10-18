<x-admin.layout title="Kategoriyi düzenle">
    <div class="card">
        <div class="card-header"><h2 class="card-title">{{ $category->name }}</h2></div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.documents.categories.update', $category) }}">
                @include('documents::categories.partials.form')
            </form>
        </div>
    </div>
</x-admin.layout>
