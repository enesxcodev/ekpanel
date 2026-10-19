<x-admin.layout title="Ürün kategorisi ekle">
    <div class="card">
        <div class="card-header"><h2 class="card-title">Yeni ürün kategorisi</h2></div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.products.categories.store') }}" enctype="multipart/form-data">
                @include('products::categories.partials.form')
            </form>
        </div>
    </div>
</x-admin.layout>
