<x-admin.layout title="Kategori ekle">
    <div class="card"><div class="card-body">
        <form method="POST" action="{{ route('admin.pages.categories.store') }}">
            @include('pages::categories.partials.form')
        </form>
    </div></div>
</x-admin.layout>
