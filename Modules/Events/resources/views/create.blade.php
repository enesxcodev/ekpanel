<x-admin.layout title="Etkinlik ekle">
    <form method="POST" action="{{ route('admin.events.store') }}" class="card">
        <div class="card-body">@include('events::_form')</div>
        <div class="card-footer text-end"><button class="btn btn-primary" type="submit">Kaydet</button></div>
    </form>
</x-admin.layout>
