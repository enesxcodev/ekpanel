<x-admin.layout title="Belge ekle">
    <div class="card">
        <div class="card-header"><h2 class="card-title">Yeni belge</h2></div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.documents.store') }}" enctype="multipart/form-data">
                @include('documents::partials.form')
            </form>
        </div>
    </div>
</x-admin.layout>
