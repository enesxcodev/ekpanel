<x-admin.layout title="Kategori düzenle">
    <div class="card">
        <div class="card-header"><h2 class="card-title">Kategori düzenle</h2></div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.branches.categories.update', $category) }}">
                @include('branches::categories.partials.form')
            </form>
        </div>
    </div>
</x-admin.layout>
