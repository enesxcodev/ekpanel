<x-admin.layout title="Hizmet kategorisi ekle">
    <div class="card">
        <div class="card-header"><h2 class="card-title">Yeni hizmet kategorisi</h2></div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.services.categories.store') }}" enctype="multipart/form-data">
                @include('services::categories.partials.form')
            </form>
        </div>
    </div>
</x-admin.layout>
