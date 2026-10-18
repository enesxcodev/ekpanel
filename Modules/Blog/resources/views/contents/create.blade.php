<x-admin.layout title="İçerik ekle">
    <form method="POST" action="{{ route('admin.blog.store') }}" enctype="multipart/form-data">
        @include('blog::contents.partials.form')
    </form>
</x-admin.layout>
