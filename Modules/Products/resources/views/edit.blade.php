<x-admin.layout title="Ürün düzenle">
    <div class="card">
        <div class="card-header"><h2 class="card-title">Ürün düzenle</h2></div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data">
                @include('products::partials.form')
            </form>
        </div>
    </div>
</x-admin.layout>
