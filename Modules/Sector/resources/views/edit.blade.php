<x-admin.layout title="Sektör düzenle">
    <form method="POST" action="{{ route('admin.sector.update', ['sector' => $sector, 'lang' => $selectedLanguage?->code]) }}" enctype="multipart/form-data">
        @include('sector::partials.form')
    </form>
</x-admin.layout>
