<aside id="layout-menu" class="layout-menu-horizontal menu-horizontal menu bg-menu-theme flex-grow-0">
    <div class="container-xxl d-flex h-100">

        <ul class="menu-inner">
            @role('admin')
            <li class="menu-item {{ Request::is('/') ? 'active' : '' }}">
                <a href="{{route('dashboard')}}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-home-circle"></i>
                    <div data-i18n="Dashboard">Dashboard</div>
                </a>
            </li>

            <li class="menu-item {{ Request::is('users*') ? 'active' : '' }}">
                <a href="{{route('users.index')}}" class="menu-link">
                    <i class="user-icon tf-icons bx bx-user"></i>
                    <div data-i18n="Пользователи">Пользователи</div>
                </a>
            </li>
            <li class="menu-item {{ Request::is('bot-users*') ? 'active' : '' }}">
                <a href="{{route('bot-users.index')}}" class="menu-link">
                    <i class='bx bx-bot' ></i>
                    <div data-i18n="Пользователи бота">Пользователи бота</div>
                </a>
            </li>

            <li class="menu-item {{ Request::is('sport-types*') ? 'active' : '' }}">
                <a href="{{route('sport-types.index')}}" class="menu-link">
                    <i class="fa-solid fa-person-skating"></i>
                    <div data-i18n="Виды спорта" class="ms-1">Виды спорта</div>
                </a>
            </li>
            @endrole

            @if(auth()->user()->hasRole('owner stadium') || auth()->user()->hasRole('admin'))
                <li class="menu-item {{ Request::is('stadiums*') ? 'active' : '' }}">
                    <a href="{{route('stadiums.index')}}" class="menu-link">
                        <i class="fa-solid fa-futbol"></i>
                        <div data-i18n="Стадион" class="ms-1">Стадион</div>
                    </a>
                </li>

                <li class="menu-item {{ Request::is('courts*') ? 'active' : '' }}">
                    <a href="{{route('courts.index')}}" class="menu-link">
                        <i class="fa-regular fa-court-sport"></i>
                        <div data-i18n="Корт" class="ms-1">Корт</div>
                    </a>
                </li>

                    <li class="menu-item {{ Request::is('schedule*') ? 'active' : '' }}">
                        <a href="{{route('schedule.index')}}" class="menu-link">
                            <i class='bx bx-spreadsheet'></i>
                            <div data-i18n="Расписания" class="ms-1">Расписания</div>
                        </a>
                    </li>
            @endif

            <li class="menu-item {{ Request::is('bookings*') ? 'active' : '' }}">
                <a href="{{route('bookings.index')}}" class="menu-link">
                    <i class="fa-solid fa-calendar-days"></i>
                    <div data-i18n="Бронирования" class="ms-1">Бронирования</div>
                </a>
            </li>

        </ul>

    </div>
</aside>
