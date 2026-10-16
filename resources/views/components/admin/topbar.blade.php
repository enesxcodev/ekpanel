<header class="navbar navbar-expand-md d-print-none">
    <div class="container-xl">
        <div class="navbar-nav flex-row order-md-last ms-auto">
            <div class="nav-item dropdown">
                <button
                    class="btn btn-ghost-secondary d-flex align-items-center gap-2"
                    type="button"
                    data-bs-toggle="dropdown"
                    aria-expanded="false"
                >
                    <span class="avatar avatar-sm">{{ mb_substr(auth('admin')->user()->name, 0, 1) }}</span>
                    <span class="d-none d-sm-inline">{{ auth('admin')->user()->name }} {{ auth('admin')->user()->lastname }}</span>
                </button>
                <div class="dropdown-menu dropdown-menu-end">
                    <a class="dropdown-item" href="{{ route('admin.dashboard') }}">Dashboard</a>
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item">Çıkış yap</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>
