<x-admin.layout title="Sektör ekle">
    <div class="card">
        <div class="card-header"><h2 class="card-title">Yeni sektör</h2></div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.sector.store') }}" enctype="multipart/form-data">
                @include('sector::partials.form')
            </form>
        </div>
    </div>
</x-admin.layout>
