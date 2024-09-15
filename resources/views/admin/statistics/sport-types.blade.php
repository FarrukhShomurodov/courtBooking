@extends('admin.layouts.app')

@section('title')
    <title>{{'Frest - '. __('sportType.statistics') }}</title>
@endsection

@section('content')
    <div class="row mb-3">
        <h6 class="py-3 breadcrumb-wrapper mb-4">
            <span class="text-muted fw-light"><a class="text-muted"
                                                 href="{{route('dashboard')}}">{{  __('menu.Dashboard') }}</a> /</span> {{ __('sportType.statistics') }}
        </h6>
        <div class="col-12 text-end">
            <a href="{{ route('statistics.sport.type.export') }}" class="btn btn-success btn-sm">
                <i class="fas fa-file-export me-2"></i>{{ __('dashboard.statistics_export') }}
            </a>
        </div>
    </div>
    <div class="card">
        <div class="d-flex justify-content-between align-items-center p-3">
            <h5 class="card-header">{{ __('sportType.statistics') }}</h5>
            <form method="GET" action="{{ route('statistics.sport.type') }}">
                <div class="d-flex">
                    <div class="d-flex flex-row align-items-center " style="margin-right: 10px">
                        <label class="me-2">От: </label>
                        <input onchange="this.form.submit()" name="date_from" type="date" class="form-control"
                               value="{{ request('date_from') }}">
                    </div>
                    <div class="d-flex flex-row align-items-center " style="margin-right: 10px">
                        <label class="me-2">До: </label>
                        <input onchange="this.form.submit()" name="date_to" type="date" class="form-control"
                               value="{{ request('date_to') }}">
                    </div>
                    <div class="me-2">
                        <select id="select2"
                                class="select2 form-select"
                                name="sport-type-id"
                                onchange="this.form.submit()"
                                tabindex="-1" aria-hidden="true" style="margin-right: 10px">
                            <option value="all" {{ request('sport-type-id') == 'all' ? 'selected' : '' }}>
                                {{ __('stadium.all_sport_types') }}
                            </option>
                            @foreach($allSportTypes as $sportType)
                                <option value="{{ $sportType->id }}"
                                    {{ request('sport-type-id') == $sportType->id ? 'selected' : '' }}>
                                    {{ $sportType->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @role('admin')
                    <div class="me-2">
                        <select id="select2"
                                class="select2 form-select"
                                name="stadium-id"
                                onchange="this.form.submit()"
                                tabindex="-1" aria-hidden="true" style="margin-right: 10px">
                            <option value="all" {{ request('stadium-id-id') == 'all' ? 'selected' : '' }}>
                                {{ __('court.select_stadium') }}
                            </option>
                            @foreach($stadiums as $stadium)
                                <option value="{{ $stadium->id }}"
                                    {{ request('stadium-id') == $stadium->id ? 'selected' : '' }}>
                                    {{ $stadium->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @endrole

                </div>
            </form>
        </div>
        <div class="card-datatable table-responsive">
            <table class="datatables-users table border-top">
                <thead>
                <tr>
                    <th>@lang('sportType.name')</th>
                    <th>@lang('book.all_book')</th>
                    <th>@lang('court.total_revenue')</th>
                    <th>@lang('court.manual_revenue')</th>
                    <th>@lang('court.bot_revenue')</th>
                    <th>@lang('dashboard.Дата наибольшего бронирования')</th>
                    <th>@lang('dashboard.Cамый загруженный временной интервал')</th>
                    <th>@lang('stadium.unbooked_hours')</th>
                </tr>
                </thead>
                <tbody>
                @foreach($statistics as $statistic)
                    <tr>
                        <td>{{ $statistic['spotType']->name }}</td>
                        <td>{{ $statistic['statistic']['total_bookings'] }}</td>

                        <td>{{ is_float($statistic['statistic']['total_revenue']) ? round($statistic['statistic']['total_revenue']) : $statistic['statistic']['total_revenue'] }}</td>
                        <td>{{ is_float($statistic['statistic']['manual_revenue']) ? round($statistic['statistic']['manual_revenue']) : $statistic['statistic']['manual_revenue'] }}</td>
                        <td>{{ is_float($statistic['statistic']['bot_revenue']) ? round($statistic['statistic']['bot_revenue']) : $statistic['statistic']['bot_revenue'] }}</td>

                        <td>{{ $statistic['statistic']['most_booked_date'] ?? '-' }}</td>
                        <td>
                            @if($statistic['statistic']['most_booked_time_slot'])
                                {{ $statistic['statistic']['most_booked_time_slot']['start_time'] }}
                                - {{ $statistic['statistic']['most_booked_time_slot']['end_time'] }}
                            @else
                                -
                            @endif
                        </td>
                        <td>{{ $statistic['statistic']['unbooked_hours'] }}</td>
                    </tr>
                @endforeach

                <tr style="font-weight: bold;">
                    <td>@lang('court.total')</td>
                    <td>{{ $totalStatistics['total_bookings'] }}</td>
                    <td>{{ round($totalStatistics['total_revenue']) }}</td>
                    <td>{{ round($totalStatistics['manual_revenue']) }}</td>
                    <td>{{ round($totalStatistics['bot_revenue']) }}</td>
                    <td>-</td>
                    <td>-</td>
                    <td>{{ $totalStatistics['unbooked_hours'] }}</td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
@endsection
