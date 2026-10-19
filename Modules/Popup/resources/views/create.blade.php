<x-admin.layout title="Popup ekle">
    <div class="card">
        <div class="card-header"><h2 class="card-title">Yeni popup</h2></div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.popup.store') }}" enctype="multipart/form-data">
                @include('popup::partials.form')
            </form>
        </div>
    </div>
</x-admin.layout>
