<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
{{--    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">--}}
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">

    <title>Findz</title>
    <link rel="stylesheet" href="{{ asset('css/findz/style.css') }}"/>
    <link rel="stylesheet" href="{{ asset('css/findz/findz.css') }}"/>
    @yield('extra-css')
{{--    <script src="https://telegram.org/js/telegram-web-app.js"></script>--}}
    <script src="https://telegram.org/js/telegram-web-app.js?1"></script>
    <script>
        window.addEventListener('DOMContentLoaded', async (event) => {
            let tg = window.Telegram.WebApp;
            tg.expand();
            let userData = tg.initDataUnsafe;
            let chatID = userData.user.id;

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

            // Обновите ссылку на "Мои брони"
            let myBookingsLink = document.getElementById('myBookingsLink');
            if (myBookingsLink) {
                @if($currentSportTypeId)
                    myBookingsLink.href = `https://st40.online/telegram/mybookings?sportType={{$currentSportTypeId}}&bot_user_id=${chatID}`;
                @endif
            }

            @if($currentSportTypeId)

            // Обновите href изображения, если нужно
            let myBookingsImg = document.getElementById('myBookingsImg');
            if (myBookingsImg) {
                myBookingsImg.onclick = function() {
                    location.href = `https://st40.online/telegram/mybookings?sportType={{$currentSportTypeId}}&bot_user_id=${chatID}`;
                };
            }
            @endif

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

@if ($currentRouteName == 'webapp' || $currentRouteName == 'findz.coaches.filter.sport.type' || $currentRouteName == 'findz.stadiums.filter.sport.type' || $currentRouteName == 'findz.mybookings')
    <footer class="w-100">
        <ul class="d-flex row align-items-center justify-content-around" style="padding: 0px !important;">
            <li class="d-flex col align-items-center  @if($currentRouteName == 'webapp' || $currentRouteName == 'findz.stadiums.filter.sport.type') footer_active @endif">
                <img
                    class="pointer"
                    onclick="location.href='{{ route('webapp', ['sportType' => $currentSportTypeId, 'date' => request('date'), 'start_time' => request('start_time'), 'end_time' => request('end_time')]) }}' "
                    src="@if($currentRouteName == 'webapp'  || $currentRouteName == 'findz.stadiums.filter.sport.type') {{ asset('img/findz/icons/courts_active.svg') }} @else {{ asset('img/findz/icons/courts.svg') }} @endif"
                    alt="footer icon">
                <a href="{{ route('webapp', ['sportType' => $currentSportTypeId, 'date' => request('date'), 'start_time' => request('start_time'), 'end_time' => request('end_time')]) }}">Площадки</a>
            </li>
{{--            <li class="d-flex col align-items-center @if($currentRouteName == 'findz.coaches.filter.sport.type') footer_active @endif">--}}
{{--                <img--}}
{{--                    class="pointer"--}}
{{--                    onclick="location.href='{{ route('findz.coaches.filter.sport.type', ['sportType' => $currentSportTypeId ?? App\Models\SportType::first()->id, 'date' => request('date'), 'start_time' => request('start_time'), 'end_time' => request('end_time')]) }}'"--}}
{{--                    src="@if( $currentRouteName == 'findz.coaches.filter.sport.type') {{  asset('img/findz/icons/coach_active.svg') }} @else {{  asset('img/findz/icons/coach.svg') }} @endif"--}}
{{--                    alt="footer icon">--}}
{{--                <a href="{{ route('findz.coaches.filter.sport.type', ['sportType' => $currentSportTypeId ?? App\Models\SportType::first()->id, 'date' => request('date'), 'start_time' => request('start_time'), 'end_time' => request('end_time')]) }}">Тренера</a>--}}

{{--            </li>--}}
            <li class="d-flex col align-items-center @if($currentRouteName == 'findz.mybookings') footer_active @endif">
                <img id="myBookingsImg" class="pointer"
                     src="@if($currentRouteName == 'findz.mybookings') {{ asset('img/findz/icons/bookings_active.svg') }} @else {{ asset('img/findz/icons/booking.svg') }} @endif"
                     alt="footer icon">
                <a id="myBookingsLink" href="#" class="mybookings">Мои брони</a>
            </li>

        </ul>
    </footer>
@else
    @yield('footer')
@endif


<script src="{{ asset('vendor/libs/jquery/jquery.js') }}"></script>
<script>
    console.log($('.mybookings'))
</script>
<script src="{{ asset('vendor/libs/flatpickr/flatpickr.js') }}"></script>
<script src="https://npmcdn.com/flatpickr/dist/l10n/ru.js"></script>
@yield('extra-scripts')

</body>
</html>
