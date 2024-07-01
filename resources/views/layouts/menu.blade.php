<aside id="layout-menu" class="layout-menu-horizontal menu-horizontal menu bg-menu-theme flex-grow-0">
    <div class="container-xxl d-flex h-100">

        <ul class="menu-inner">

            <!-- Dashboards -->
            <li class="menu-item {{ Request::is('/') ? 'active' : '' }}">
                <a href="{{route('users')}}" class="menu-link">
                    <i class="user-icon tf-icons bx bx-user"></i>
                    <div data-i18n="Пользователи">Пользователи</div>
                </a>
            </li>

            <li class="menu-item {{ Route::is('houses.index') ? 'active' : '' }}">
                <a href="{{ route('houses.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-building-house"></i>
                    <div data-i18n="Дома">Дома</div>
                </a>
            </li>

            <li class="menu-item {{ Route::is('floors.index') ? 'active' : '' }}">
                <a href="{{ route('floors.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-layer"></i>
                    <div data-i18n="Этажи">Этажи</div>
                </a>
            </li>

            <li class="menu-item {{ Route::is('apartments.index') ? 'active' : '' }}">
                <a href="{{ route('apartments.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-home-alt"></i>
                    <div data-i18n="Квартиры">Квартиры </div>
                </a>
            </li>
        </ul>

    </div>
</aside>
