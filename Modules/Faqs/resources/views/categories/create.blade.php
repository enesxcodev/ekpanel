<x-admin.layout title="Kategori ekle">
    <div class="card">
        <div class="card-header"><h2 class="card-title">Yeni SSS kategorisi</h2></div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.faqs.categories.store') }}">
                @include('faqs::categories.partials.form')
            </form>
        </div>
    </div>
</x-admin.layout>
