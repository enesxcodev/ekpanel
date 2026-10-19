<x-admin.layout title="Kullanıcıyı düzenle">
    <div class="card">
        <div class="card-header"><h2 class="card-title">{{ $user->name }} {{ $user->surname }}</h2></div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.web-users.update', $user) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <x-admin.form.input name="name" label="Ad" :value="$user->name" required maxlength="255" />
                <x-admin.form.input name="surname" label="Soyad" :value="$user->surname" required maxlength="255" />
                <x-admin.form.input name="email" label="E-posta" type="email" :value="$user->email" required maxlength="255" />
                <x-admin.form.input name="username" label="Kullanıcı adı" :value="$user->username" maxlength="255" />
                <x-admin.form.input name="password" label="Yeni şifre (değiştirmek için doldurun)" type="password" autocomplete="new-password" />
                <div class="mb-3">
                    <label class="form-label" for="profile_image">Profil resmi</label>
                    @include('users::partials.photo')
                    <input type="file" class="form-control @error('profile_image') is-invalid @enderror" id="profile_image" name="profile_image" accept="image/jpeg,image/png,image/webp">
                    @error('profile_image') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                @if($user->profile_image)
                    <x-admin.form.boolean name="remove_profile_image" label="Profil resmini kaldır" :value="false" />
                @endif
                <x-admin.form.boolean name="is_active" label="Durum" :value="$user->is_active" />
                <div class="d-flex gap-2 mt-3">
                    <button type="submit" class="btn btn-primary">Değişiklikleri kaydet</button>
                    <a href="{{ route('admin.web-users.index') }}" class="btn btn-link">Vazgeç</a>
                </div>
            </form>
        </div>
    </div>
</x-admin.layout>
