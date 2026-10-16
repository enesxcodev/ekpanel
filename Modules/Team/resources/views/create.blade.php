<x-admin.layout title="Ekip ekle">
    <div class="card">
        <div class="card-header"><h2 class="card-title">Yeni ekip üyesi</h2></div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.team.store') }}" enctype="multipart/form-data">
                @include('team::partials.form')
            </form>
        </div>
    </div>
</x-admin.layout>
