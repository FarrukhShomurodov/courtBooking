<nav class="layout-navbar navbar navbar-expand-xl align-items-center bg-navbar-theme" id="layout-navbar">
    <div class="container-xxl">
        <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
            <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
                <i class="bx bx-menu bx-sm"></i>
            </a>
        </div>

        <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
            <div class="navbar-nav align-items-center">
                <div class="nav-item navbar-search-wrapper mb-0">
                  <li class="nav-item dropdown-language dropdown me-2 me-xl-0">
                    <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                        <img src="{{ app()->getLocale() == 'ru' ? asset('img/flags/ru.png') : asset('img/flags/uz.png') }}" alt="Language Flag" class="me-2" style="width: 20px; height: 15px; border-radius: 2px">
                        <span class="align-middle d-sm-inline-block d-none">{{ app()->getLocale() == "ru" ? "Русский" : "O'zbekcha" }}</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="{{ url('set-lang/ru') }}">
                                <img src="{{ asset('img/flags/ru.png') }}" alt="Russian Flag" class="me-2" style="width: 20px; height: 15px; border-radius: 2px">
                                <span class="align-middle">Русский</span>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="{{ url('set-lang/uz') }}">
                                <img src="{{ asset('img/flags/uz.png') }}" alt="Uzbek Flag" class="me-2" style="width: 20px; height: 15px; border-radius: 2px">
                                <span class="align-middle">O'zbekcha</span>
                            </a>
                        </li>
                    </ul>
                </li>
                </div>
            </div>

            <ul class="navbar-nav flex-row align-items-center ms-auto">
                <!-- User -->
                <li class="nav-item navbar-dropdown dropdown-user dropdown">
                    <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                        <div class="avatar avatar-online">
                            @if(auth()->user()->avatar)
                                <img class="avatar-initial rounded-circle" src="{{ \Illuminate\Support\Facades\Storage::url(auth()->user()->avatar) }}" alt="user avatar">
                            @else
                                <span class="avatar-initial rounded-circle bg-success">{{ auth()->user()->name[0] }}</span>
                            @endif
                        </div>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="">
                                <div class="d-flex">
                                    <div class="flex-shrink-0 me-3">
                                        <div class="avatar avatar-online">
                                            @if(auth()->user()->avatar)
                                                <img class="avatar-initial rounded-circle" src="{{ \Illuminate\Support\Facades\Storage::url(auth()->user()->avatar) }}" alt="user avatar">
                                            @else
                                                <span class="avatar-initial rounded-circle bg-success">{{ auth()->user()->name[0] }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <span class="fw-semibold d-block lh-1">{{ auth()->user()->name }} {{ auth()->user()->second_name }}</span>
                                        <small>{{ auth()->user()->roles->pluck('name')[0] }}</small>
                                    </div>
                                </div>
                            </a>
                        </li>
                        <li>
                            <div class="dropdown-divider"></div>
                        </li>
                        <li>
                            <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="bx bx-power-off me-2"></i>
                                <span class="align-middle">@lang('dashboard.logout')</span>
                            </a>

                            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                @csrf
                                @method('POST')
                            </form>
                        </li>
                    </ul>
                </li>
                <!--/ User -->
            </ul>
        </div>

        <!-- Search Small Screens -->
        <div class="navbar-search-wrapper search-input-wrapper container-xxl d-none">
            <input type="text" class="form-control search-input border-0" placeholder="Search..." aria-label="Search...">
            <i class="bx bx-x bx-sm search-toggler cursor-pointer"></i>
        </div>
    </div>
</nav>
