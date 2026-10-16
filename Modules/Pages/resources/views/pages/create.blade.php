<x-admin.layout title="Sayfa ekle">
    <div class="card"><div class="card-body">
        <form method="POST" action="{{ route('admin.pages.store') }}" enctype="multipart/form-data">
            @include('pages::pages.partials.form')
        </form>
    </div></div>
</x-admin.layout>
