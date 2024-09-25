@extends('admin.layouts.app')

@section('title')
    <title>{{'Findz - '. __('user.bot_user') }}</title>
@endsection

@section('content')
    <div class="card">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-header"> {{__('user.bot_user')}}</h5>
            <div class="row me-1">
                <div class="col-12 text-end ">
                    <a href="{{ route('statistics.bot-users') }}" class="btn btn-success btn-sm">
                        <i class="fas fa-file-export me-1"></i>{{ __('dashboard.statistics_export') }}
                    </a>
                </div>
            </div>
        </div>
        <div class="card-datatable table-responsive">
            <table class="datatables-users table border-top">
                <thead>
                <tr>
                    <th>id</th>
                    <th>chat_id</th>
                    <th>first_name</th>
                    <th>second_name</th>
                    <th>uname</th>
                    <th>typed_name</th>
                    <th>phone</th>
                    <th>sms_code</th>
                    <th>step</th>
                    <th>isactive</th>
                    <th>book_count</th>
                    <th>{{ __('user.bot_user_created_at') }}</th>
                </tr>
                </thead>
                <tbody>

                @php
                    $userCount = 1
                @endphp

                @foreach($botUsers as $user)
                    <tr>
                        <td>{{ $userCount++ }}</td>
                        <td>{{ $user->chat_id }}</td>
                        <td>{{ $user->first_name }}</td>
                        <td>{{ $user->second_name }}</td>
                        <td>{{ $user->uname }}</td>
                        <td>{{ $user->typed_name }}</td>
                        <td>{{ $user->phone }}</td>
                        <td>{{ $user->sms_code }}</td>
                        <td>{{ $user->step}}</td>
                        <td>
                            <label class="switch">
                                <input type="checkbox" class="switch-input" data-user-id="{{ $user->id }}"
                                       @if($user->isactive) checked @endif>
                                <span class="switch-toggle-slider">
                                    <span class="switch-on"></span>
                                    <span class="switch-off"></span>
                                </span>
                            </label>
                        </td>
                        <td>
                        {{ $user->bookings->sum(function ($booking) {
                            return $booking->bookingItems->where('status', 'paid')->count();
                        }) }}
                        </td>
                        <td>{{ $user->created_at}}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function () {
            $('.switch-input').on('change', function () {
                let userId = $(this).data('user-id');
                let isActive = $(this).is(':checked') ? 1 : 0;

                $.ajax({
                    url: `/api/bot-users/${userId}/is-active`,
                    method: 'put',
                    data: {
                        _token: '{{ csrf_token() }}',
                        isactive: isActive
                    },
                    success: function (res) {
                    },
                    error: function (error) {
                    }
                });
            });
        });
    </script>
@endsection
