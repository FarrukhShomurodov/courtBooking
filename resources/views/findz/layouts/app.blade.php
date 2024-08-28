<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Findz</title>
    <link rel="stylesheet" href="{{ secure_asset('css/findz/style.css') }}"/>
    <link rel="stylesheet" href="{{ secure_asset('css/findz/findz.css') }}"/>
    @yield('extra-css')
    <script src="https://telegram.org/js/telegram-web-app.js"></script>
    <script>
        window.addEventListener('DOMContentLoaded', async (event) => {
            let tg = window.Telegram.WebApp;
            let userData = tg.initDataUnsafe;

            // Функция для проверки наличия пользователя на сервере
            async function checkUser() {
                try {
                    let response = await fetch('/api/has-bot-user', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(userData)
                    });
                    let result = await response.json();
                    if (result.exists) {
                        tg.expand();
                    } else {
                        tg.sendData('User not found');
                        window.location.href = 'https://t.me/cuourts_bokking_bot';
                    }
                } catch (error) {
                    console.error('Error:', error);
                }
            }

            if (Object.keys(userData).length === 0 || typeof userData.user === 'undefined') {
                window.location.href = 'https://t.me/cuourts_bokking_bot';
            } else {
                await checkUser();
            }
        });
    </script>
    @yield('extra-js')
</head>
<body>

@yield('header')

@yield('content')

@php
    $currentRouteName = Route::currentRouteName();
@endphp

@if ($currentRouteName == 'webapp' || $currentRouteName == 'findz.coaches.filter.sport.type' || $currentRouteName == 'findz.courts.filter.sport.type' || $currentRouteName == 'findz.mybookings')
    <footer class="w-100">
        <ul class="d-flex row align-items-center justify-content-between" style="padding: 0px !important;">
            <li class="d-flex col align-items-center  @if($currentRouteName == 'webapp' || $currentRouteName == 'findz.courts.filter.sport.type') footer_active @endif">
                <img
                    class="pointer"
                    onclick="location.href='{{ route('webapp', ['sportType' => $currentSportTypeId, 'date' => request('date'), 'start_time' => request('start_time'), 'end_time' => request('end_time')]) }}' "
                    src="@if($currentRouteName == 'webapp'  || $currentRouteName == 'findz.courts.filter.sport.type') {{ secure_asset('img/findz/icons/courts_active.svg') }} @else {{ secure_asset('img/findz/icons/courts.svg') }} @endif"
                    alt="footer icon">
                <a href="{{ route('webapp', ['sportType' => $currentSportTypeId, 'date' => request('date'), 'start_time' => request('start_time'), 'end_time' => request('end_time')]) }}">Площадки</a>
            </li>
            <li class="d-flex col align-items-center @if($currentRouteName == 'findz.coaches.filter.sport.type') footer_active @endif">
                <img
                    class="pointer"
                    onclick="location.href='{{ route('findz.coaches.filter.sport.type', ['sportType' => $currentSportTypeId ?? App\Models\SportType::first()->id, 'date' => request('date'), 'start_time' => request('start_time'), 'end_time' => request('end_time')]) }}'"
                    src="@if( $currentRouteName == 'findz.coaches.filter.sport.type') {{  secure_asset('img/findz/icons/coach_active.svg') }} @else {{  secure_asset('img/findz/icons/coach.svg') }} @endif"
                    alt="footer icon">
                <a href="{{ route('findz.coaches.filter.sport.type', ['sportType' => $currentSportTypeId ?? App\Models\SportType::first()->id, 'date' => request('date'), 'start_time' => request('start_time'), 'end_time' => request('end_time')]) }}">Тренера</a>

            </li>
            <li class="d-flex col align-items-center @if($currentRouteName == 'findz.mybookings') footer_active @endif">
                <img class="pointer"
                     onclick="location.href='{{ route('findz.mybookings', ['sportType' => $currentSportTypeId]) }}'"
                     src="@if( $currentRouteName == 'findz.mybookings') {{  secure_asset('img/findz/icons/bookings_active.svg') }} @else {{ secure_asset('img/findz/icons/booking.svg') }} @endif">
                <a href="{{ route('findz.mybookings', ['sportType' => $currentSportTypeId]) }}">Мои брони</a>
            </li>
        </ul>
    </footer>
@else
    @yield('footer')
@endif

<script src="{{ secure_asset('vendor/libs/jquery/jquery.js') }}"></script>
{{--<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>--}}
<script src="{{ secure_asset('vendor/libs/flatpickr/flatpickr.js') }}"></script>
<script src="https://npmcdn.com/flatpickr/dist/l10n/ru.js"></script>
@yield('extra-scripts')

</body>
</html>
