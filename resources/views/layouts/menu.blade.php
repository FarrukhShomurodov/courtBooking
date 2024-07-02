<aside id="layout-menu" class="layout-menu-horizontal menu-horizontal menu bg-menu-theme flex-grow-0">
    <div class="container-xxl d-flex h-100">

        <ul class="menu-inner">

            <li class="menu-item {{ Request::is('/') ? 'active' : '' }}">
                <a href="{{route('dashboard')}}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-home-circle"></i>
                    <div data-i18n="Dashboard">Dashboard</div>
                </a>
            </li>

            <li class="menu-item {{ Request::is('users') ? 'active' : '' }}">
                <a href="{{route('users.index')}}" class="menu-link">
                    <i class="user-icon tf-icons bx bx-user"></i>
                    <div data-i18n="Пользователи">Пользователи</div>
                </a>
            </li>

            <li class="menu-item {{ Request::is('bot-users') ? 'active' : '' }}">
                <a href="{{route('bot-users.index')}}" class="menu-link">
                    <i class="user-icon tf-icons bx bx-user"></i>
                    <div data-i18n="Пользователи бота">Пользователи бота</div>
                </a>
            </li>

            <li class="menu-item {{ Request::is('sport-types') ? 'active' : '' }}">
                <a href="{{route('sport-types.index')}}" class="menu-link">
                    <i class="user-icon tf-icons bx bx-user"></i>
                    <div data-i18n="Виды спорта">Виды спорта</div>
                </a>
            </li>
        </ul>

    </div>
</aside>
