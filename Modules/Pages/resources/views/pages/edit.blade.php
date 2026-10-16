<x-admin.layout title="Sayfayı düzenle">
    <div class="card"><div class="card-body">
        <form method="POST" action="{{ route('admin.pages.update', $page) }}" enctype="multipart/form-data">
            @include('pages::pages.partials.form')
        </form>
    </div></div>
</x-admin.layout>
