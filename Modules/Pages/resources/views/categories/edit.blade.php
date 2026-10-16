<x-admin.layout title="Kategoriyi düzenle">
    <div class="card"><div class="card-body">
        <form method="POST" action="{{ route('admin.pages.categories.update', $category) }}">
            @include('pages::categories.partials.form')
        </form>
    </div></div>
</x-admin.layout>
