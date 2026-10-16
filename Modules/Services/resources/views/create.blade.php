<x-admin.layout title="Hizmet ekle">
    <div class="card">
        <div class="card-header"><h2 class="card-title">Yeni hizmet</h2></div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.services.store') }}" enctype="multipart/form-data">
                @include('services::partials.form')
            </form>
        </div>
    </div>
</x-admin.layout>
