<x-admin.layout title="Soruyu düzenle">
    <div class="card">
        <div class="card-header"><h2 class="card-title">{{ $faq->name }}</h2></div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.faqs.update', $faq) }}">
                @include('faqs::partials.form')
            </form>
        </div>
    </div>
</x-admin.layout>
