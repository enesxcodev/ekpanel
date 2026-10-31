<x-admin.layout title="Slider düzenle">
    <div class="card">
        <div class="card-header"><h2 class="card-title">{{ $translation?->title ?? 'Slider düzenle' }}</h2></div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.slider.update', $slider) }}" enctype="multipart/form-data">
                @include('slider::partials.form')
            </form>
        </div>
    </div>
</x-admin.layout>
