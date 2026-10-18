<!doctype html>
<html lang="tr" data-bs-theme="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Admin Giriş · {{ $companyName }}</title>
        @vite(['resources/css/admin.css', 'resources/css/admin-dark.css', 'resources/css/admin-login.css', 'resources/js/panel.js'])
    </head>
    <body class="admin-login-page">
        <x-admin.notifications />
        <main class="admin-login-layout">
            <section class="admin-login-form-side" aria-labelledby="login-title">
                <div class="admin-login-form-wrap">
                    <a class="admin-login-brand" href="{{ route('admin.login') }}" aria-label="{{ $companyName }} giriş sayfası">
                        <img class="admin-login-brand-logo" src="{{ asset('images/ekagent-logo-dark.png') }}" alt="{{ $companyName }}">
                    </a>

                    <div class="admin-login-content">
                        <p class="admin-login-eyebrow">YÖNETİM PANELİ</p>
                        <h1 id="login-title">Tekrar hoş geldiniz</h1>
                        <p class="admin-login-intro">Devam etmek için yönetici hesabınızla giriş yapın.</p>

                        <form action="{{ route('admin.login.store') }}" method="POST" class="admin-login-form">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label" for="email">E-posta adresi</label>
                                <input id="email" type="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" placeholder="admin@example.com" autocomplete="username" maxlength="255" required autofocus @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
                                @error('email')
                                    <div id="email-error" class="invalid-feedback" role="alert">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-4">
                                <label class="form-label" for="password">Şifre</label>
                                <input id="password" type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="Şifreniz" autocomplete="current-password" required @error('password') aria-invalid="true" aria-describedby="password-error" @enderror>
                                @error('password')
                                    <div id="password-error" class="invalid-feedback" role="alert">{{ $message }}</div>
                                @enderror
                            </div>
                            <button type="submit" class="btn btn-primary w-100 admin-login-submit">Giriş yap</button>
                        </form>
                    </div>

                    <p class="admin-login-footer">Güvenli yönetim alanı <span aria-hidden="true">·</span> {{ $companyName }}</p>
                </div>
            </section>

            <aside class="admin-login-cover" aria-label="Yönetim paneli hakkında">
                <div class="admin-login-cover-copy">
                    <span class="admin-login-cover-label">{{ $companyName }} · ADMIN</span>
                    <h2>İşinizi tek bir yerden yönetin.</h2>
                    <p>İçerikleriniz, ayarlarınız ve yönetim araçlarınız her zaman elinizin altında.</p>
                </div>
                <span class="admin-login-cover-caption">{{ $companyName }} yönetim deneyimi</span>
            </aside>
        </main>
    </body>
</html>
