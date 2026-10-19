<header class="navbar navbar-expand-md d-print-none admin-topbar">
    <div class="container-xl flex-nowrap gap-2">
        <div class="navbar-nav flex-row align-items-center gap-1 ms-auto">
            <div class="nav-item">
                <button class="nav-link px-2" type="button" data-admin-theme-toggle aria-label="Koyu temaya geç" title="Koyu temaya geç">
                    <span data-theme-icon="moon"><x-admin.icon name="moon" /></span>
                    <span data-theme-icon="sun" hidden><x-admin.icon name="sun" /></span>
                </button>
            </div>
            <div class="nav-item dropdown" data-notification-header="{{ route('admin.notifications.header') }}">
                <button class="nav-link px-2" type="button" id="admin-notifications-toggle" data-bs-toggle="dropdown" aria-expanded="false" aria-controls="admin-notifications" aria-label="Bildirimler">
                    <x-admin.icon name="bell" />
                    <span class="badge bg-red text-white ms-1" data-notification-count @if($unreadNotificationCount === 0) hidden @endif>{{ $unreadNotificationCount }}</span>
                </button>
                <div class="dropdown-menu dropdown-menu-end p-0 admin-notifications" id="admin-notifications" aria-labelledby="admin-notifications-toggle">
                    @include('admin.notifications.dropdown', ['notificationItems' => $notificationItems, 'unreadNotificationCount' => $unreadNotificationCount])
                </div>
            </div>
            <div class="nav-item dropdown ms-2">
                <button class="nav-link d-flex align-items-center gap-2 p-0" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Kullanıcı menüsü">
                    <span class="avatar avatar-sm bg-primary-lt">{{ mb_substr(auth('admin')->user()->name, 0, 1) }}</span>
                    <span class="d-none d-sm-block text-start admin-topbar-user">{{ auth('admin')->user()->name }} {{ auth('admin')->user()->lastname }}</span>
                </button>
                <div class="dropdown-menu dropdown-menu-end">
                    @can('viewAny', \App\Models\Admin\Admin::class)
                        <a class="dropdown-item" href="{{ route('admin.users.index') }}">Profil</a>
                    @endcan
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item">Çıkış yap</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>
