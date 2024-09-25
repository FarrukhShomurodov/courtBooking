@extends('admin.layouts.app')

@section('title')
    <title>{{'Findz - '. __('stadium.statistics') }}</title>
@endsection

@section('content')
    <div class="row mb-3">
        <h6 class="py-3 breadcrumb-wrapper mb-4">
            <span class="text-muted fw-light"><a class="text-muted"
                                                 href="{{route('dashboard')}}">{{  __('menu.Dashboard') }}</a> /</span> @lang('stadium.statistics')
        </h6>
        <div class="col-12 text-end">
            <a href="{{ route('statistics.stadiums.export') }}" class="btn btn-success btn-sm">
                <i class="fas fa-file-export me-2"></i>{{ __('dashboard.statistics_export') }}
            </a>
        </div>
    </div>
    <div class="card">
        <div class="d-flex justify-content-between align-items-center p-3">
            <h5 class="card-header">@lang('stadium.statistics')</h5>
            @can('manage stadiums')
                <form method="GET" action="{{ route('statistics.stadiums') }}">
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
                                @foreach($sportTypes as $sportType)
                                    <option value="{{ $sportType->id }}"
                                        {{ request('sport-type-id') == $sportType->id ? 'selected' : '' }}>
                                        {{ $sportType->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        {{--    Owners  --}}
{{--                        <div>--}}
{{--                            <select id="select22"--}}
{{--                                    class="select2 form-select"--}}
{{--                                    name="owner-id"--}}
{{--                                    onchange="this.form.submit()"--}}
{{--                                    tabindex="-1" aria-hidden="true" style="margin-right: 10px">--}}
{{--                                @foreach($ownerStadium as $owner)--}}
{{--                                    <option value="{{ $owner->id }}"--}}
{{--                                        {{ request('owner-id') == $owner->id ? 'selected' : '' }}>--}}
{{--                                        {{ $owner->name }}--}}
{{--                                    </option>--}}
{{--                                @endforeach--}}
{{--                            </select>--}}
{{--                        </div>--}}
                    </div>
                </form>
            @endcan
        </div>
        <div class="card-datatable table-responsive">
            <table class="datatables-users table border-top">
                <thead>
                <tr>
                    <th>@lang('stadium.stadium')</th>
                    <th>@lang('stadium.total_hours')</th>
                    <th>@lang('stadium.bot_hours')</th>
                    <th>@lang('stadium.manual_hours')</th>
                    <th>@lang('stadium.total_revenue')</th>
                    <th>@lang('stadium.bot_revenue')</th>
                    <th>@lang('stadium.manual_revenue')</th>
                    <th>@lang('stadium.unbooked_hours')</th>
                </tr>
                </thead>
                <tbody>
                @foreach($statistics as $statistic)
                    <tr>
                        <td>{{ $statistic['stadium']->name }}</td>
                        <td>{{ $statistic['statistic']['total_book_count'] }}</td>
                        <td>{{ $statistic['statistic']['bot_book_count'] }}</td>
                        <td>{{ $statistic['statistic']['manual_book_count'] }}</td>

                        <td>{{ number_format(round($statistic['statistic']['total_revenue']), 0 , ' ', ' ') }}</td>
                        <td>{{ number_format(round($statistic['statistic']['bot_revenue']), 0 , ' ', ' ') }}</td>
                        <td>{{ number_format(round($statistic['statistic']['manual_revenue']), 0 , ' ', ' ')  }}</td>
                        <td>{{ $statistic['statistic']['unbooked_hours'] }}</td>
                    </tr>
                @endforeach

                @role('admin')
                <tr style="font-weight: bold;">
                    <td>@lang('court.total')</td>
                    <td>{{ $totalStatistics['total_book_count'] }}</td>
                    <td>{{ $totalStatistics['bot_book_count'] }}</td>
                    <td>{{ $totalStatistics['manual_book_count'] }}</td>
                    <td>{{ number_format(round($totalStatistics['total_revenue']), 0 , ' ', ' ') }}</td>
                    <td>{{ number_format (round($totalStatistics['bot_revenue']), 0 , ' ', ' ') }}</td>
                    <td>{{ number_format(round($totalStatistics['manual_revenue']), 0 , ' ', ' ') }}</td>
                    <td>{{ $totalStatistics['unbooked_hours'] }}</td>
                </tr>
                @endrole
                </tbody>
            </table>
        </div>
    </div>
@endsection
