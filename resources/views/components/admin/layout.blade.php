@props([
    'title' => 'Admin',
    'fluid' => false,
])

<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="light">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ $title }} · {{ $companyName }}</title>

        @vite(['resources/css/admin.css', 'resources/js/panel.js'])
    </head>
    <body>
        <x-admin.notifications />
        <div class="page">
            <x-admin.sidebar :company-name="$companyName">{{ $sidebar ?? '' }}</x-admin.sidebar>

            <div class="page-wrapper">
                @isset($topbar)
                    {{ $topbar }}
                @endisset

                <div class="page-header d-print-none">
                    <div class="{{ $fluid ? 'container-fluid px-lg-4' : 'container-xl' }}">
                        <div class="row g-2 align-items-center">
                            <div class="col">
                                <x-admin.page-title :title="$title" />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="page-body">
                    <div class="{{ $fluid ? 'container-fluid px-lg-4' : 'container-xl' }}">
                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>
        @stack('scripts')
    </body>
</html>
