<x-admin.layout title="Popup düzenle">
    <div class="card">
        <div class="card-header"><h2 class="card-title">{{ $translation?->title ?? 'Popup çevirisi' }}</h2></div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.popup.update', $popup) }}" enctype="multipart/form-data">
                @include('popup::partials.form')
            </form>
        </div>
    </div>
</x-admin.layout>
