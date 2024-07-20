@extends('layouts.app')

@php
    use  Illuminate\Support\Carbon;
@endphp

@section('content')
    <div class="card">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-header">Все брони</h5>
        </div>
        <div class="card-datatable table-responsive">
            <table class="datatables-users table border-top">
                <thead>
                <tr>
                    <th>Id</th>
                    <th>Стадион</th>
                    <th>Корт</th>
                    <th>Дата</th>
                    <th>Время</th>
                    <th>Продолжительность</th>
                    <th>Сумма</th>
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
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
