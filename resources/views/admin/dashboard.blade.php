@extends('admin.layouts.app')

@section('title')
    <title>Dashboard - Analytics | Findz - Bootstrap Admin</title>
@endsection


@section('content')
    <div class="row mb-3">
        <div class="col-12 text-end">
            <a href="{{ route('statistics.export') }}" class="btn btn-success btn-sm">
                <i class="fas fa-file-export me-1"></i>{{ __('dashboard.statistics_export') }}
            </a>
        </div>
    </div>

    <div class="row">
        @role('admin')
        <div class="col-md-6 col-xl-4 mb-3">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">{{ __('dashboard.Все пользователи') }}</h5>
                </div>
                <div class="card-body">
                    <h2 class="display-4 fw-normal mb-3">{{ $statistics['total_user_count'] }}</h2>
                    <p class="mb-2">{{ __('dashboard.Текущая деятельность') }}</p>
                    @php
                        $total = $statistics['total_user_count'];
                        $userPercent = $total > 0 ? ($statistics['user_count'] / $total) * 100 : 0;
                        $botUserPercent = $total > 0 ? ($statistics['bot_user_count'] / $total) * 100 : 0;
                    @endphp
                    <div class="progress mb-3" style="height: 8px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ $userPercent }}%"
                             aria-valuenow="{{ $userPercent }}" aria-valuemin="0" aria-valuemax="100"></div>
                        <div class="progress-bar bg-danger" role="progressbar" style="width: {{ $botUserPercent }}%"
                             aria-valuenow="{{ $botUserPercent }}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <ul class="list-unstyled">
                        <li class="mb-2 d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <span class="badge bg-success me-2"></span> {{ __('dashboard.Пользователи сайта') }}
                            </div>
                            <span>{{ $statistics['user_count'] }}</span>
                        </li>
                        <li class="mb-2 d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <span class="badge bg-danger me-2"></span> {{ __('dashboard.Пользователи бота') }}
                            </div>
                            <span>{{ $statistics['bot_user_count'] }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-12 mb-3">

            <div class="card border-0 text-center">
                <div class="card-body p-3">
                    <h3 class="mb-1">{{ $statistics['stadium_count'] }}</h3>
                    <p class="mb-2 text-muted">{{ __('dashboard.Стадионы') }}</p>
                    <a class="btn btn-outline-primary btn-sm" href="{{ route('statistics.stadiums') }}">
                        {{ __('dashboard.view_stadium_statistics') }}
                    </a>
                </div>
            </div>

            <div class="card border-0 text-center mt-3">
                <div class="card-body p-3">
                    <h3 class="mb-1">{{ $statistics['court_count'] }}</h3>
                    <p class="mb-2 text-muted">{{ __('dashboard.Корты') }}</p>
                    <a class="btn btn-outline-primary btn-sm" href="{{ route('statistics.courts') }}">
                        {{ __('dashboard.view_court_statistics') }}
                    </a>
                </div>
            </div>
        </div>
        @endrole


        <div class="col-lg-4 col-12 mb-3">
            @role('owner stadium')
            <div class="card border-0 text-center mt-3">
                <div class="card-body p-3">
                    <h3 class="mb-1">{{ $statistics['court_count'] }}</h3>
                    <p class="mb-2 text-muted">{{ __('dashboard.Корты') }}</p>
                    <a class="btn btn-outline-primary btn-sm" href="{{ route('statistics.courts') }}">
                        {{ __('dashboard.view_court_statistics') }}
                    </a>
                </div>
            </div>
            @endrole


            <div class="card border-0 text-center">
                <div class="card-body p-3">
                    <p class="mb-1 text-muted">{{ __('dashboard.Брони') }}</p>
                    <h2 class="mb-0">{{ $statistics['booking_count'] }}</h2>
                </div>
            </div>

            <div class="card border-0 text-center mt-3">
                <div class="card-body p-3">
                    <p class="mb-1 text-muted">{{ __('dashboard.Дата наибольшего бронирования') }}</p>
                    <h2 class="mb-0">{{ $statistics['most_booking_date'] }}</h2>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-12 mb-3">
            <div class="card border-0 text-center">
                <div class="card-body p-3">
                    <p class="mb-1 text-muted">{{ __('dashboard.Cамый загруженный временной интервал') }}</p>
                    @if(is_string($statistics['most_booked_time_slot']))
                        <h2 class="mb-0">{{ $statistics['most_booked_time_slot'] }}</h2>
                    @else
                        <h2 class="mb-0">{{ $statistics['most_booked_time_slot']->start_time }}
                            - {{ $statistics['most_booked_time_slot']->end_time }}</h2>
                    @endif
                </div>
            </div>

            <div class="card border-0 text-center mt-3">
                <div class="card-body p-3">
                    <h2 class="mb-0">{{ $statistics['sport_type_count'] }}</h2>
                    <p class="mb-1 text-muted">{{ __('dashboard.Типы спорта') }}</p>
                    <a class="btn btn-outline-primary btn-sm" href="{{ route('statistics.sport.type') }}">
                        {{ __('dashboard.view_sport_type_statistics') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
