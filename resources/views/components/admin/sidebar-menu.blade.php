@props(['menu'])

@if (isset($menu['children']))
    <li class="nav-item">
        <a class="nav-link dropdown-toggle {{ $menu['is_active'] ? 'active' : '' }}"
           href="#{{ $menu['id'] }}" data-bs-toggle="collapse" role="button"
           aria-expanded="{{ $menu['is_active'] ? 'true' : 'false' }}" aria-controls="{{ $menu['id'] }}">
            @if (! empty($menu['icon']))
                <span class="nav-link-icon d-md-none d-lg-inline-block"><x-admin.icon :name="$menu['icon']" /></span>
            @endif
            <span class="nav-link-title">{{ $menu['label'] }}</span>
        </a>
        <div id="{{ $menu['id'] }}" class="collapse {{ $menu['is_active'] ? 'show' : '' }}">
            <ul class="navbar-nav ps-3">
                @foreach ($menu['children'] as $child)
                    <x-admin.sidebar-menu :menu="$child" />
                @endforeach
            </ul>
        </div>
    </li>
@else
    <li class="nav-item">
        <a class="nav-link {{ $menu['is_active'] ? 'active' : '' }}" href="{{ route($menu['route']) }}"
           @if ($menu['is_active']) aria-current="page" @endif>
            @if (! empty($menu['icon']))
                <span class="nav-link-icon d-md-none d-lg-inline-block"><x-admin.icon :name="$menu['icon']" /></span>
            @endif
            <span class="nav-link-title">{{ $menu['label'] }}</span>
        </a>
    </li>
@endif
