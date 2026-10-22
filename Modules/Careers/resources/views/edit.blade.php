<x-admin.layout title="İlan düzenle">
    <form method="POST" action="{{ route('admin.careers.update', $posting) }}" class="card">
        <div class="card-body">@include('careers::_form')</div>
        <div class="card-footer text-end"><button class="btn btn-primary" type="submit">Kaydet</button></div>
    </form>
</x-admin.layout>
