@extends('admin.layouts.app')

@php
    use  Illuminate\Support\Carbon;
@endphp

@section('content')
    <h6 class="py-3 breadcrumb-wrapper mb-4">
        <span class="text-muted fw-light"><a class="text-muted"
                                             href="{{route('bookings.index')}}">{{  __('menu.Бронирования') }}</a> /</span>{{__('book.all_book')}}
    </h6>
    <div class="card">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-header">{{__('book.all_book')}}</h5>
        </div>
        <div class="card-datatable table-responsive">
            <table class="datatables-users table border-top">
                <thead>
                <tr>
                    <th>Id</th>
                    <th>{{__('book.stadium')}}</th>
                    <th>{{__('book.court')}}</th>
                    <th>{{__('book.date')}}</th>
                    <th>{{__('book.time')}}</th>
                    <th>{{__('book.duration')}}</th>
                    <th>{{__('book.sum')}}</th>
                    <th>{{__('book.source')}}</th>
                </tr>
                </thead>
                <tbody>

                @foreach($bookings as $booking)

                    @php
                        $start_time = Carbon::parse($booking->start_time);
                        $end_time = Carbon::parse($booking->end_time);
                        $diff = $start_time->diff($end_time);
                    @endphp
                    <tr>
                        <td>{{ $booking->id }}</td>
                        <td>{{ $booking->court->name }}</td>
                        <td>{{ $booking->court->stadium->name }}</td>
                        <td>{{ $booking->date }}</td>
                        <td>{{ $booking->start_time }} - {{ $booking->end_time }}</td>
                        <td>{{ $diff->h }} ч</td>
                        <td>{{ $booking->price }}</td>
                        <td>{{ $booking->source == 'manual' ? 'Manual' : 'Findz' }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
