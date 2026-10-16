<x-admin.layout title="Sektör düzenle">
    <div class="card">
        <div class="card-header"><h2 class="card-title">{{ $sector->title }}</h2></div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.sector.update', $sector) }}" enctype="multipart/form-data">
                @include('sector::partials.form')
            </form>
        </div>
    </div>
</x-admin.layout>
