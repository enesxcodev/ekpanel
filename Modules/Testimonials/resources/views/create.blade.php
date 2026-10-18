<x-admin.layout title="Yorum ekle">
    <div class="card">
        <div class="card-header"><h2 class="card-title">Yeni müşteri yorumu</h2></div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.testimonials.store') }}" enctype="multipart/form-data">
                @include('testimonials::partials.form')
            </form>
        </div>
    </div>
</x-admin.layout>
