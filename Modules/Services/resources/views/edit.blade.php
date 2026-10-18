<x-admin.layout title="Hizmet düzenle">
    <div class="card">
        <div class="card-header"><h2 class="card-title">Hizmet düzenle</h2></div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.services.update', $service) }}" enctype="multipart/form-data">
                @include('services::partials.form')
            </form>
        </div>
    </div>
</x-admin.layout>
