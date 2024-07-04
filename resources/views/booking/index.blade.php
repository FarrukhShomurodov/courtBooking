@extends('layouts.app')

@section('content')
    <div class="card">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-header">Бронирования</h5>
            <a href="{{ route('bookings.create') }}" class="btn btn-primary" style="margin-right: 22px;">Создать</a>
        </div>
        <div class="card-datatable table-responsive">
            <table class="datatables-users table border-top">
                <thead>
                <tr>
                    <th>Корт</th>
                    <th>Пользователь</th>
                    <th>Дата</th>
                    <th>Час</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                @foreach ($bookings as $booking)
                    <tr>
                        <td>{{ $booking->court->name }}</td>
                        <td>{{ $booking->user->name }}</td>
                        <td>{{ $booking->day->date }}</td>
                        <td>{{ $booking->hour->start_time }} - {{ $booking->hour->end_time }}</td>
                        <td>
                            <div class="d-inline-block text-nowrap">
                                <form action="{{ route('bookings.destroy', $booking->id) }}" method="POST"
                                      style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-icon delete-record"><i class="bx bx-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
