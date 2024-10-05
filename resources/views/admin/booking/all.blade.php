@extends('admin.layouts.app')

@php
    use  Illuminate\Support\Carbon;
@endphp

@section('title')
    <title>{{'Findz - '. __('book.all_book') }}</title>
@endsection

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
            @if ($errors->any())
                <div class="alert alert-solid-danger" role="alert">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </div>
            @endif
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
                    <th>{{__('book.status')}}</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                @php
                    $count = 1
                @endphp
                @foreach($bookings as $booking)

                    @php
                        $start_time = Carbon::parse($booking->start_time);
                        $end_time = $booking->end_time === '00:00:00'? Carbon::parse($booking->end_time)->addDay() : Carbon::parse($booking->end_time);
                        $diff = $start_time->diff($end_time);
                    @endphp
                    <tr>
                        <td>{{ $count++ }}</td>
                        <td>{{ $booking->court->name }}</td>
                        <td>{{ $booking->court->stadium->name }}</td>
                        <td>{{ $booking->date }}</td>
                        <td>{{ $booking->start_time }} - {{ $booking->end_time }}</td>
                        <td>{{ $diff->h }} ч</td>
                        <td>{{ number_format(round($booking->price), 0 , ' ', ' ') }}</td>
                        <td>{{ $booking->source == 'manual' ? 'Manual' : 'Findz' }}</td>
                        <td>{{ $booking->status }}</td>
                            <td>
                                @if($booking->source === 'manual')

                                <div class="d-inline-block text-nowrap">
                                    <form action="{{ route('bookings.destroy', $booking->id) }}" method="POST"
                                          style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-icon delete-record"><i class="bx bx-trash"></i>
                                        </button>
                                    </form>
                                </div>
                                @endif

                            </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
