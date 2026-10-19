<x-admin.layout title="Ürün kategorisi düzenle">
    <div class="card">
        <div class="card-header"><h2 class="card-title">{{ $category->name }}</h2></div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.products.categories.update', $category) }}" enctype="multipart/form-data">
                @include('products::categories.partials.form')
            </form>
        </div>
    </div>
</x-admin.layout>
