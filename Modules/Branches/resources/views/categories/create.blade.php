<x-admin.layout title="Kategori ekle">
    <div class="card">
        <div class="card-header"><h2 class="card-title">Kategori ekle</h2></div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.branches.categories.store') }}" data-branches-form>
                @include('branches::categories.partials.form')
            </form>
        </div>
    </div>
</x-admin.layout>
