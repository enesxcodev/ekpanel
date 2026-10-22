<x-admin.layout title="Etkinlik düzenle">
    <form method="POST" action="{{ route('admin.events.update', $event) }}" class="card">
        <div class="card-body">@include('events::_form')</div>
        <div class="card-footer text-end"><button class="btn btn-primary" type="submit">Kaydet</button></div>
    </form>
</x-admin.layout>
