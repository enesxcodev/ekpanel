<x-admin.layout title="Slider ekle">
    <div class="card">
        <div class="card-header"><h2 class="card-title">Yeni slider</h2></div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.slider.store') }}" enctype="multipart/form-data">
                @include('slider::partials.form')
            </form>
        </div>
    </div>
</x-admin.layout>
