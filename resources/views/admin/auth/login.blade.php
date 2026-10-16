<!doctype html>
<html lang="tr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Admin Giriş · {{ config('app.name', 'Laravel') }}</title>
        @vite(['resources/css/admin.css', 'resources/js/panel.js'])
    </head>
    <body class="d-flex flex-column">
        <x-admin.notifications />
        <div class="page page-center">
            <div class="container container-tight py-4">
                <div class="text-center mb-4">
                    <span class="navbar-brand navbar-brand-autodark">{{ config('app.name', 'Laravel') }}</span>
                </div>
                <div class="card card-md">
                    <div class="card-body">
                        <h1 class="h2 text-center mb-4">Yönetim paneline giriş</h1>
                        <form action="{{ route('admin.login.store') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label" for="email">E-posta adresi</label>
                                <input id="email" type="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" placeholder="admin@example.com" autocomplete="username" maxlength="255" required autofocus @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
                                @error('email')
                                    <div id="email-error" class="invalid-feedback" role="alert">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="password">Şifre</label>
                                <input id="password" type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="Şifreniz" autocomplete="current-password" required @error('password') aria-invalid="true" aria-describedby="password-error" @enderror>
                                @error('password')
                                    <div id="password-error" class="invalid-feedback" role="alert">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-footer">
                                <button type="submit" class="btn btn-primary w-100">Giriş yap</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
