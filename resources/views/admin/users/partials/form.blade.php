@csrf
@if (isset($admin))
    @method('PUT')
@endif

<div class="row g-3">
    <div class="col-12 col-md-6">
        <div class="mb-3">
            <label for="name" class="form-label">Ad</label>
            <input id="name" name="name" value="{{ old('name', $admin->name ?? '') }}" class="form-control @error('name') is-invalid @enderror" maxlength="255" autocomplete="given-name" required>
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>

    <div class="col-12 col-md-6">
        <div class="mb-3">
            <label for="lastname" class="form-label">Soyad</label>
            <input id="lastname" name="lastname" value="{{ old('lastname', $admin->lastname ?? '') }}" class="form-control @error('lastname') is-invalid @enderror" maxlength="255" autocomplete="family-name" required>
            @error('lastname')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>

    <div class="col-12 col-md-6">
        <div class="mb-3">
            <label for="email" class="form-label">E-posta</label>
            <input id="email" type="email" name="email" value="{{ old('email', $admin->email ?? '') }}" class="form-control @error('email') is-invalid @enderror" maxlength="255" autocomplete="email" required>
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>

    <div class="col-12 col-md-6">
        <div class="mb-3">
            <label for="type" class="form-label">Yönetici türü</label>
            <select id="type" name="type" class="form-select @error('type') is-invalid @enderror" required>
                @foreach ($types as $type)
                    @continue(auth('admin')->user()->type !== \App\Enums\AdminType\AdminTypeEnum::SuperAdmin && $type === \App\Enums\AdminType\AdminTypeEnum::SuperAdmin)
                    <option value="{{ $type->value }}" @selected((string) old('type', isset($admin) ? $admin->type->value : \App\Enums\AdminType\AdminTypeEnum::Admin->value) === (string) $type->value)>{{ $type->label() }}</option>
                @endforeach
            </select>
            @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>

    <div class="col-12 col-md-6">
        <div class="mb-3">
            <label class="form-label">Durum</label>
            @php($selectedStatus = (string) old('status', isset($admin) ? (int) $admin->status : 1))
            <div class="form-selectgroup @error('status') is-invalid @enderror">
                <label class="form-selectgroup-item">
                    <input type="radio" name="status" value="1" class="form-selectgroup-input" @checked($selectedStatus === '1') required>
                    <span class="form-selectgroup-label">Aktif</span>
                </label>
                <label class="form-selectgroup-item">
                    <input type="radio" name="status" value="0" class="form-selectgroup-input" @checked($selectedStatus === '0') required>
                    <span class="form-selectgroup-label">Pasif</span>
                </label>
            </div>
            @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>

    <div class="col-12 col-md-6">
        <div class="mb-3">
            <label for="password" class="form-label">{{ isset($admin) ? 'Yeni şifre' : 'Şifre' }}</label>
            <input id="password" type="password" name="password" class="form-control @error('password') is-invalid @enderror" autocomplete="new-password" minlength="12" @required(! isset($admin))>
            @if (isset($admin))<div class="form-hint">Şifreyi değiştirmek istemiyorsanız boş bırakın.</div>@endif
            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>

    <div class="col-12 col-md-6">
        <div class="mb-3">
            <label for="password_confirmation" class="form-label">Şifreyi doğrula</label>
            <input id="password_confirmation" type="password" name="password_confirmation" class="form-control" autocomplete="new-password" minlength="12" @required(! isset($admin))>
        </div>
    </div>
</div>

<button type="submit" class="btn btn-primary">{{ isset($admin) ? 'Değişiklikleri kaydet' : 'Yönetici ekle' }}</button>
<a href="{{ route('admin.users.index') }}" class="btn btn-link">Vazgeç</a>
