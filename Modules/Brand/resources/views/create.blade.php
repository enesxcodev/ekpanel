<x-admin.layout title="Marka ekle">
    <div class="card">
        <div class="card-header"><h2 class="card-title">Marka ekle</h2></div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.brand.store') }}" enctype="multipart/form-data">
                @include('brand::partials.form')
            </form>
        </div>
    </div>
</x-admin.layout>
