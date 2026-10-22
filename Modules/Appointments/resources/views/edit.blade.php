<x-admin.layout title="Randevu düzenle">
    <form method="POST" action="{{ route('admin.appointments.update', $appointment) }}" class="card">
        <div class="card-body">@include('appointments::_form')</div>
        <div class="card-footer text-end"><button class="btn btn-primary" type="submit">Kaydet</button></div>
    </form>
</x-admin.layout>
