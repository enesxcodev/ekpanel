<x-admin.layout title="Soru ekle">
    <div class="card">
        <div class="card-header"><h2 class="card-title">Yeni SSS kaydı</h2></div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.faqs.store') }}">
                @include('faqs::partials.form')
            </form>
        </div>
    </div>
</x-admin.layout>
