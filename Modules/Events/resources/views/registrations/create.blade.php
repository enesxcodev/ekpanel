<x-admin.layout title="Katılımcı ekle">
    <form method="post" action="{{ route('admin.events.registrations.store') }}" class="card">
        <div class="card-body">
            @csrf
            <x-admin.form.select name="event_id" label="Etkinlik" :options="$events->map(fn ($event) => ['value' => $event->id, 'label' => $event->title])->all()" required />
            <x-admin.form.input name="attendee_name" label="Katılımcı adı" required />
            <x-admin.form.input name="email" label="E-posta" type="email" required />
            <x-admin.form.input name="phone" label="Telefon" />
            <x-admin.form.select name="status" label="Durum" :options="[['value' => 'registered', 'label' => 'Kayıtlı'], ['value' => 'cancelled', 'label' => 'İptal'], ['value' => 'attended', 'label' => 'Katıldı']]" value="registered" required />
        </div>
        <div class="card-footer text-end"><button class="btn btn-primary" type="submit">Kaydet</button></div>
    </form>
</x-admin.layout>
