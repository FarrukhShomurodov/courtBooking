@extends('admin.layouts.app')

@section('title')
    <title>{{'Frest - '. __('court.statistics') }}</title>
@endsection

@section('content')
    <div class="row mb-3">
        <h6 class="py-3 breadcrumb-wrapper mb-4">
            <span class="text-muted fw-light"><a class="text-muted" href="{{route('dashboard')}}">{{  __('menu.Dashboard') }}</a> /</span> {{ __('court.statistics') }}
        </h6>
        <div class="col-12 text-end">
            <a href="{{ route('statistics.courts.export') }}" class="btn btn-success btn-sm">
                <i class="fas fa-file-export me-2"></i>{{ __('dashboard.statistics_export') }}
            </a>
        </div>
    </div>
    <div class="card">
        <div class="d-flex justify-content-between align-items-center p-3">
            <h5 class="card-header">{{ __('court.statistics') }}</h5>
            <form method="GET" action="{{ route('statistics.courts') }}">
                <div class="d-flex">
                    <div style="margin-right: 10px">
                        <input onchange="this.form.submit()" name="date" type="date" class="form-control" value="{{ request('date') }}">
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
                    @can('manage stadiums')
                        <div>
                            <select id="select22"
                                    class="select2 form-select"
                                    name="owner-id"
                                    onchange="this.form.submit()"
                                    tabindex="-1" aria-hidden="true" style="margin-right: 10px">
                                @foreach($ownerStadium as $owner)
                                    <option value="{{ $owner->id }}"
                                        {{ request('owner-id') == $owner->id ? 'selected' : '' }}>
                                        {{ $owner->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endcan
                </div>
            </form>
        </div>
        <div class="card-datatable table-responsive">
            <table class="datatables-users table border-top">
                <thead>
                <tr>
                    <th>@lang('court.courts')</th>
                    <th>@lang('court.total_hours')</th>
                    <th>@lang('court.bot_hours')</th>
                    <th>@lang('court.manual_hours')</th>
                    <th>@lang('court.total_revenue')</th>
                    <th>@lang('court.bot_revenue')</th>
                    <th>@lang('court.manual_revenue')</th>
                </tr>
                </thead>
                <tbody>
                @foreach($statistics as $statistic)
                    <tr>
                        <td>{{ $statistic['court']->name }}</td>
                        <td>{{ $statistic['statistic']['total_book_count'] }}</td>
                        <td>{{ $statistic['statistic']['bot_book_count'] }}</td>
                        <td>{{ $statistic['statistic']['manual_book_count'] }}</td>

                        <td>{{ is_float($statistic['statistic']['total_revenue']) ? round($statistic['statistic']['total_revenue']) : $statistic['statistic']['total_revenue'] }}</td>
                        <td>{{ is_float($statistic['statistic']['bot_revenue']) ? round($statistic['statistic']['bot_revenue']) : $statistic['statistic']['bot_revenue'] }}</td>
                        <td>{{ is_float($statistic['statistic']['manual_revenue']) ? round($statistic['statistic']['manual_revenue']) : $statistic['statistic']['manual_revenue'] }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
