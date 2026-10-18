<x-admin.layout title="Proje düzenle">
    <div class="card"><div class="card-header"><h2 class="card-title">Proje düzenle</h2></div><div class="card-body">
        <form method="POST" action="{{ route('admin.projects.update', $project) }}" enctype="multipart/form-data" data-project-form>
            @include('projects::partials.form')
        </form>
    </div></div>
</x-admin.layout>