@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-md-6  col-xl-4  mb-xl-0">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-2">Все пользователи </h5>
                    <h1 class="display-6 fw-normal mb-0">{{ $statistics['total_user_count'] }}</h1>
                </div>
                <div class="card-body">
                    <span class="d-block mb-2">Текущая деятельность</span>
                    @php
                        $total = $statistics['total_user_count'];
                        $userPercent = $total > 0 ? ($statistics['user_count'] / $total) * 100 : 0;
                        $botUserPercent = $total > 0 ? ($statistics['bot_user_count'] / $total) * 100 : 0;
                    @endphp
                    <div class="progress progress-stacked mb-3 mb-xl-5" style="height:8px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ $userPercent }}%"
                             aria-valuenow="{{ $userPercent }}" aria-valuemin="0" aria-valuemax="100"></div>
                        <div class="progress-bar bg-danger" role="progressbar" style="width: {{ $botUserPercent }}%"
                             aria-valuenow="{{ $botUserPercent }}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <ul class="p-0 m-0">
                        <li class="mb-3 d-flex justify-content-between">
                            <div class="d-flex align-items-center lh-1 me-3">
                                <span class="badge badge-dot bg-success me-2"></span> Пользователи сайта
                            </div>
                            <div class="d-flex gap-3">
                                <span>{{ $statistics['user_count'] }}</span>
                            </div>
                        </li>
                        <li class="mb-3 d-flex justify-content-between">
                            <div class="d-flex align-items-center lh-1 me-3">
                                <span class="badge badge-dot bg-danger me-2"></span> Пользователи бота
                            </div>
                            <div class="d-flex gap-3">
                                <span>{{ $statistics['bot_user_count'] }}</span>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-12 mb-xl-0">
            <div class="card h-50">
                <div class="card-body text-center">
                    <span class="d-block text-nowrap">Стадионы</span>
                    <h2 class="mb-0">{{ $statistics['stadium_count'] }}</h2>
                </div>
            </div>

            <div class="card h-50 mt-1">
                <div class="card-body text-center">
                    <span class="d-block text-nowrap">Корты</span>
                    <h2 class="mb-0">{{ $statistics['court_count'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-12 mb-xl-0">
            <div class="card h-50">
                <div class="card-body text-center">
                    <span class="d-block text-nowrap">Брони</span>
                    <h2 class="mb-0">{{ $statistics['booking_count'] }}</h2>
                </div>
            </div>
            <div class="card h-50 mt-1">
                <div class="card-body text-center">
                    <span class="d-block text-nowrap">Дата наибольшего бронирования</span>
                    <h2 class="mb-0">{{ $statistics['most_booking_date'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-12 mb-xl-0 mt-1">
            <div class="card h-50 mt-1">
                <div class="card-body text-center">
                    <span class="d-block text-nowrap">Cамый загруженный временной интервал</span>
                    @if(!isset($statistics['most_booked_time_slot']))
                        <h2 class="mb-0">{{ $statistics['most_booked_time_slot'] }}</h2>

                    @else
                        <h2 class="mb-0">{{ $statistics['most_booked_time_slot']->start_time }}
                            - {{ $statistics['most_booked_time_slot']->end_time }}</h2>
                    @endif
                </div>
            </div>
            <div class="card h-50 mt-1">
                <div class="card-body text-center">
                    <span class="d-block text-nowrap">Типы спорта</span>
                    <h2 class="mb-0">{{ $statistics['sport_type_count'] }}</h2>
                </div>
            </div>
        </div>
    </div>
@endsection
