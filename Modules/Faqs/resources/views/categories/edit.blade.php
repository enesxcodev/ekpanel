<x-admin.layout title="Kategoriyi düzenle">
    <div class="card">
        <div class="card-header"><h2 class="card-title">{{ $translation?->name ?? 'Çeviri eksik (#'.$category->id.')' }}</h2></div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.faqs.categories.update', $category) }}">
                @include('faqs::categories.partials.form')
            </form>
        </div>
    </div>
</x-admin.layout>
