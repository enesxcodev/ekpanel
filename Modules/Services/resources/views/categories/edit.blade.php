<x-admin.layout title="Hizmet kategorisi düzenle">
    <div class="card">
        <div class="card-header"><h2 class="card-title">{{ $category->name }}</h2></div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.services.categories.update', $category) }}" enctype="multipart/form-data">
                @include('services::categories.partials.form')
            </form>
        </div>
    </div>
</x-admin.layout>
