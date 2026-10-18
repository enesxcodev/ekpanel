<x-admin.layout title="Belgeyi düzenle">
    <div class="card">
        <div class="card-header"><h2 class="card-title">{{ $document->name }}</h2></div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.documents.update', $document) }}" enctype="multipart/form-data">
                @include('documents::partials.form')
            </form>
        </div>
    </div>
</x-admin.layout>
