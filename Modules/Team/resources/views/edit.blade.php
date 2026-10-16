<x-admin.layout title="Ekip üyesini düzenle">
    <div class="card">
        <div class="card-header"><h2 class="card-title">{{ $member->name }}</h2></div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.team.update', $member) }}" enctype="multipart/form-data">
                @include('team::partials.form')
            </form>
        </div>
    </div>
</x-admin.layout>
