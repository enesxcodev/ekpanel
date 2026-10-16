<x-admin.layout title="Markayı düzenle">
    <div class="card">
        <div class="card-header"><h2 class="card-title">{{ $brand->name }}</h2></div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.brand.update', $brand) }}" enctype="multipart/form-data">
                @include('brand::partials.form')
            </form>
        </div>
    </div>
</x-admin.layout>
