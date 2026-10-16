<aside class="navbar navbar-vertical navbar-expand-md bg-dark" data-bs-theme="dark">
    <div class="container-fluid">
        <button
            class="navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#admin-sidebar-menu"
            aria-controls="admin-sidebar-menu"
            aria-expanded="false"
            aria-label="Menüyü aç veya kapat"
        >
            <span class="navbar-toggler-icon"></span>
        </button>

        <h1 class="navbar-brand navbar-brand-autodark">
            <a href="{{ route('admin.dashboard') }}" class="text-reset text-decoration-none">
                {{ config('app.name', 'Laravel') }}
            </a>
        </h1>

        <div class="collapse navbar-collapse" id="admin-sidebar-menu">
            <ul class="navbar-nav pt-lg-3">
                <li class="nav-item">
                    <a
                        class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
                        href="{{ route('admin.dashboard') }}"
                        @if (request()->routeIs('admin.dashboard')) aria-current="page" @endif
                    >
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <path d="M4 4h6v8h-6z" />
                                <path d="M4 16h6v4h-6z" />
                                <path d="M14 12h6v8h-6z" />
                                <path d="M14 4h6v4h-6z" />
                            </svg>
                        </span>
                        <span class="nav-link-title">Dashboard</span>
                    </a>
                </li>
                @if (auth('admin')->user()?->can('users.menu') && auth('admin')->user()?->can('viewAny', \App\Models\Admin\Admin::class))
                    <li class="nav-item">
                        <a
                            class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}"
                            href="{{ route('admin.users.index') }}"
                            @if (request()->routeIs('admin.users.*')) aria-current="page" @endif
                        >
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M9 7m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" />
                                    <path d="M3 21v-2a4 4 0 0 1 4 -4h4" />
                                    <path d="M16 19h6" />
                                    <path d="M19 16v6" />
                                </svg>
                            </span>
                            <span class="nav-link-title">Yöneticiler</span>
                        </a>
                    </li>
                @endif
                @can('manage-admin-roles')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}" href="{{ route('admin.roles.index') }}">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M12 3l8 4v5c0 5-3.5 8-8 9-4.5-1-8-4-8-9V7l8-4z" />
                                    <path d="M9 11h6v5H9z" />
                                    <path d="M10 11V9a2 2 0 1 1 4 0v2" />
                                </svg>
                            </span>
                            <span class="nav-link-title">Roller &amp; İzinler</span>
                        </a>
                    </li>
                @endcan
                @if (auth('admin')->user()->can('settings.menu') && auth('admin')->user()->can('settings.view'))
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}" href="{{ route('admin.settings.index') }}">
                            <span class="nav-link-icon d-md-none d-lg-inline-block"><x-admin.icon name="settings" /></span>
                            <span class="nav-link-title">Ayarlar</span>
                        </a>
                    </li>
                @endif
                @foreach ($moduleMenus as $menu)
                    <x-admin.sidebar-menu :menu="$menu" />
                @endforeach
                {{ $slot }}
            </ul>
        </div>
    </div>
</aside>
