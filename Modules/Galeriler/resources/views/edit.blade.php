<x-admin.layout title="Galeri kaydı düzenle">
    <div class="card"><div class="card-header"><h2 class="card-title">Galeri kaydı düzenle</h2></div><div class="card-body">
        <form method="POST" action="{{ route('admin.galeriler.update', $item) }}" enctype="multipart/form-data" data-galeriler-form>
            @include('galeriler::partials.form')
        </form>
    </div></div>
</x-admin.layout>