<x-admin.layout title="Yorumu düzenle">
    <div class="card">
        <div class="card-header"><h2 class="card-title">{{ $translation?->customer_name ?? 'Çeviri yok' }}</h2></div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.testimonials.update', $testimonial) }}" enctype="multipart/form-data">
                @include('testimonials::partials.form')
            </form>
        </div>
    </div>
</x-admin.layout>
