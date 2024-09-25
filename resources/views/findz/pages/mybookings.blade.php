@extends('findz.layouts.app')

@section('extra-css')
    <link rel="stylesheet" href="{{asset('css/findz/mybookings.css')}}"/>
    <style>
        .container_mobile {
            margin: 65px auto 0;
        }
    </style>
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
            @foreach($bookings as $bookingItems)
                @foreach($bookingItems->bookingItems()->where('status', 'paid')->get() as $booking)
                    <div class="stadiums mt-30">
                        @if($booking->court->stadium->photos)
                            <div class="court_images">
                                <div class="scroll-container">
                                    @foreach(json_decode($booking->court->stadium->photos) as $photo)
                                        <div><img class="stadium_image"
                                                  src="{{\Illuminate\Support\Facades\Storage::url($photo)}}"
                                                  alt="court photo"/></div>
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
                            <p>{{ substr($booking->start_time, 0, 5) }} - {{ substr($booking->end_time, 0, 5) }} | {{ $booking->date}}</p>
                            <p>{{ $booking->court->stadium->name }}, {{  $booking->court->name }}</p>
                            <span>{{ round($booking->price) / 1000 }} {{ __('findz/book.currency') }}</span>
                            <span>{{ $booking->court->stadium->address }}</span>
                            @if($hoursRemaining <= 24)
                                <i>{{ __('findz/book.edit_book_info') }}</i>
                            @endif
                            @if($booking->is_edit)
                                <i>{{ __('findz/book.can_not_edit_book_more_one') }}</i>
                            @endif
                        </div>
                        <button class="cancel-btn">
                            @if($hoursRemaining >= 24)
                                @if($booking->is_edit)
                                    <a style="color: #585864;  cursor: not-allowed;">
                                        {{ __('findz/book.do_not_edit_book') }}
                                    </a>
                                @else
                                    <a href="{{ route('book.edit', ['booking' => $booking->id, 'sportType' => $currentSportTypeId]) }}">
                                        {{ __('findz/book.edit_book') }}
                                    </a>
                                @endif

                            @else
                                <a style="color: #585864;  cursor: not-allowed;">
                                    {{ __('findz/book.do_not_edit_book') }}
                                </a>
                            @endif
                        </button>
                    </div>
                @endforeach
            @endforeach
        </div>
    </div>
@endsection
@section('extra-scripts')
    <script>
        $(document).ready(function () {
            $('.scroll-container').slick({
                infinite: true,
                slidesToShow: 1,
                slidesToScroll: 1,
                dots: true,
                arrows: true,
                adaptiveHeight: true
            });

            $('.scroll-wrapper').on('wheel', function (event) {
                if (event.originalEvent.deltaY !== 0) {
                    this.scrollLeft += event.originalEvent.deltaY;
                    event.preventDefault();
                }
            });

            $('.scroll-wrapper').on('wheel', function (event) {
                if (event.originalEvent.deltaY !== 0) {
                    this.scrollLeft += event.originalEvent.deltaY;
                    event.preventDefault();
                }
            });

        });
    </script>
@endsection
