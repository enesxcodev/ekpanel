<x-admin.layout title="Şube / bayi ekle">
    <div class="card">
        <div class="card-header"><h2 class="card-title">Yeni şube / bayi</h2></div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.branches.store') }}" data-branches-form data-location-options-url="{{ $locationOptionsUrl }}">
                @include('branches::partials.form')
            </form>
        </div>
    </div>
</x-admin.layout>
