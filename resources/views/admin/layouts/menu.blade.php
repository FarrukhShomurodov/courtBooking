<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand demo ">
        <a href="{{ route('dashboard') }}" class="app-brand-link">
            <img src="{{ asset('img/icons/logo.svg') }}">
        </a>

        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
            <i class="bx menu-toggle-icon d-none d-xl-block fs-4 align-middle"></i>
            <i class="bx bx-x d-block d-xl-none bx-sm align-middle"></i>
        </a>
    </div>

    <div class="menu-divider mt-0"></div>

    <ul class="menu-inner py-1 ps ps--active-y">
        @if(auth()->user()->hasRole('owner stadium') || auth()->user()->hasRole('admin'))
            <li class="menu-item {{ Request::is('/') || Request::is('statistics*') ? 'active' : '' }}">
                <a href="{{route('dashboard')}}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-home-circle m"></i>
                    <div data-i18n="{{  __('menu.Dashboard') }}">{{  __('menu.Dashboard') }}</div>
                </a>
            </li>
        @endif

        @role('admin')
        <li class="menu-item {{ Request::is('users*') ? 'active' : '' }}">
            <a href="{{route('users.index')}}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-user-circle"></i>
                <div data-i18n="{{  __('menu.Пользователи') }}">{{  __('menu.Пользователи') }}</div>
            </a>
        </li>
        <li class="menu-item {{ Request::is('bot-users*') ? 'active' : '' }}">
            <a href="{{route('bot-users.index')}}" class="menu-link">
                <i class='menu-icon bx bx-bot'></i>
                <div data-i18n="{{  __('menu.Пользователи бота') }}">{{  __('menu.Пользователи бота') }}</div>
            </a>
        </li>

        <li class="menu-item {{ Request::is('sport-types*') ? 'active' : '' }}">
            <a href="{{route('sport-types.index')}}" class="menu-link">
                <i class="menu-icon fa-solid fa-person-skating"></i>
                <div data-i18n="{{  __('menu.Виды спорта') }}" class="ms-1">{{  __('menu.Виды спорта') }}</div>
            </a>
        </li>
        @endrole

        @if(auth()->user()->hasRole('owner stadium') || auth()->user()->hasRole('admin'))
            <li class="menu-item {{ Request::is('stadiums*') ? 'active' : '' }}">
                <a href="{{route('stadiums.index')}}" class="menu-link">
                    <i class="menu-icon fa-solid fa-futbol"></i>
                    <div data-i18n="{{  __('menu.Стадион') }}" class="ms-1">{{  __('menu.Стадион') }}</div>
                </a>
            </li>

            <li class="menu-item {{ Request::is('courts*') ? 'active' : '' }}">
                <a href="{{route('courts.index')}}" class="menu-link">
                    <img src="{{asset('img/icons/football-field-svgrepo-com.svg')}}">
                    <div data-i18n="{{  __('menu.Корт') }}" class="ms-3">{{  __('menu.Корт') }}</div>
                </a>
            </li>
        @endif
        <li class="menu-item {{ Request::is('schedule*') ? 'active' : '' }}">
            <a href="{{route('bookings.index')}}" class="menu-link">
                <i class='menu-icon bx bx-spreadsheet'></i>
                <div data-i18n="{{  __('menu.Бронирования') }}" class="ms-1">{{  __('menu.Бронирования') }}</div>
            </a>
        </li>
    </ul>
</aside>

<div class="layout-overlay layout-menu-toggle"></div>
