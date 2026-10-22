<x-admin.layout title="Aday başvurusu ekle">
    <form method="post" action="{{ route('admin.careers.applications.store') }}" class="card">
        <div class="card-body">
            @csrf
            <x-admin.form.select name="job_posting_id" label="İş ilanı" :options="$postings->map(fn ($posting) => ['value' => $posting->id, 'label' => $posting->title])->all()" required />
            <x-admin.form.input name="candidate_name" label="Aday adı" required />
            <x-admin.form.input name="email" label="E-posta" type="email" required />
            <x-admin.form.input name="phone" label="Telefon" />
            <x-admin.form.textarea name="cover_letter" label="Ön yazı" />
            <x-admin.form.select name="status" label="Durum" :options="[['value' => 'new', 'label' => 'Yeni'], ['value' => 'reviewing', 'label' => 'İnceleniyor'], ['value' => 'accepted', 'label' => 'Kabul edildi'], ['value' => 'rejected', 'label' => 'Reddedildi']]" value="new" required />
        </div>
        <div class="card-footer text-end"><button class="btn btn-primary" type="submit">Kaydet</button></div>
    </form>
</x-admin.layout>
