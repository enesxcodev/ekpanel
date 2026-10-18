<x-admin.layout title="İçeriği düzenle">
    <form method="POST" action="{{ route('admin.blog.update', $blog) }}" enctype="multipart/form-data">
        @include('blog::contents.partials.form')
    </form>
    <div class="small text-secondary mt-3">Oluşturulma: {{ $blog->created_at?->format('d.m.Y H:i') }} · Son güncelleme: {{ $blog->updated_at?->format('d.m.Y H:i') }}</div>
</x-admin.layout>
