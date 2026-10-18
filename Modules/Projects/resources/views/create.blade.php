<x-admin.layout title="Proje ekle">
    <div class="card"><div class="card-header"><h2 class="card-title">Proje ekle</h2></div><div class="card-body">
        <form method="POST" action="{{ route('admin.projects.store') }}" enctype="multipart/form-data" data-project-form>
            @include('projects::partials.form')
        </form>
    </div></div>
</x-admin.layout>