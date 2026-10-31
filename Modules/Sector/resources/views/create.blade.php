<x-admin.layout title="Sektör ekle">
    <form method="POST" action="{{ route('admin.sector.store') }}" enctype="multipart/form-data">
        @include('sector::partials.form')
    </form>
</x-admin.layout>
