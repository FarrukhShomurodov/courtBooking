@extends('findz.layouts.app')

@section('extra-css')
    <link rel="stylesheet" href="{{asset('css/findz/mybookings.css')}}"/>
@endsection

@section('header')
    <header class="d-flex row justify-content-around align-items-center booking_header">
        <h3 class="book pointer">{{ __('findz/book.bookings') }}</h3>
        <h2 class="favourite pointer" style="display:none;">{{ __('findz/book.favourites') }}</h2>
    </header>
@endsection

@section('content')
    {{--    <nav>--}}
    {{--        <button class="nav_active footer_btn">{{ __('findz/book.courts') }}</button>--}}
    {{--        <button class="footer_btn">{{ __('findz/book.trainers') }}</button>--}}
    {{--    </nav>--}}

    <div class="container_mobile">
        <div class="content">
            @foreach($bookings as $booking)
                <div class="stadiums mt-30">
                    @if($booking->court->photos)
                        <div class="court_images">
                            <div class="scroll-container">
                                @foreach(json_decode($booking->court->photos) as $photo)
                                    <img src="../storage/{{ $photo }}" alt="Sport type photo"/>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    @php
                        $bookingDateTime = Carbon\Carbon::parse($booking->date . ' ' . $booking->start_time);
                        $now = Carbon\Carbon::now();

                        $hoursRemaining = $now->diffInHours($bookingDateTime, false);
                    @endphp
                    <div class="stadium_desc mt-15">
                        <p>{{ $booking->start_time }} - {{  $booking->end_time}} | {{ $booking->date}}</p>
                        <p>{{ $booking->court->stadium->name }}, {{  $booking->court->name }}</p>
                        <span>{{ $booking->price }} {{ __('findz/book.currency') }}</span>
                        <span>{{ $booking->court->stadium->address }}</span>
                        @if($hoursRemaining <= 24)
                            <i>Перенос брони менее чем за 24 часа невозможен.</i>
                        @endif
                    </div>
                    <button class="cancel-btn">
                        @if($hoursRemaining >= 24)
                            <a href="{{ route('book.edit',$booking->id,[ 'sportType' => $currentSportTypeId]) }}">
                                {{ __('findz/book.edit_book') }}
                            </a>
                        @else
                            <a style="color: #585864;  cursor: not-allowed;">
                                {{ 'Измена Невозможна' }}
                            </a>
                        @endif
                    </button>
                </div>
            @endforeach
        </div>
    </div>
@endsection
