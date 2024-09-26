@extends('findz.layouts.app')

@if(request('date') || request('start_time') || request('end_time'))
    @section('extra-css')
        <style>
            .container_mobile {
                margin: 182px auto 0;
            }
        </style>
    @endsection
@endif


@section('header')
    <header class="d-flex row justify-content-center align-items-center">
        <h3 class="findz">FINDZ</h3>
    </header>
@endsection

@section('content')
    <nav>
        <div class="scroll-wrapper">
            <ul class="sport_types d-flex row align-items-center gap-12">
                @foreach($sportTypes as $sportType)
                    <li class="@if($sportType->id === $currentSportTypeId) nav_active @endif">
                        <a href="{{ route('findz.stadiums.filter.sport.type', ['sportType' => $sportType->id, 'date' => request('date'), 'start_time' => request('start_time'), 'end_time' => request('end_time')]) }}">{{ $sportType->name }}</a>
                    </li>
                @endforeach
            </ul>
        </div>
    </nav>

    <div class="container_mobile">
        <div class="content">
            @foreach($stadiums as $stadium)
                <div class="stadiums mt-30"
                     onclick="location.href='{{ route('findz.show.stadium', ['sportType' => $currentSportTypeId, 'stadium' => $stadium->id, 'date' => request('date'), 'start_time' => request('start_time'), 'end_time' => request('end_time')]) }}'">
                    @if($stadium->photos)
                        <div class="court_images">
                            <div class="scroll-container">
                                @foreach(json_decode($stadium->photos) as $photo)
                                    <div><img class="stadium_image" src="{{\Illuminate\Support\Facades\Storage::url($photo)}}" alt="stadium photo"/></div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    <div class="stadium_desc mt-15">
                        <p>{{ $stadium->name }}</p>
                        <span>{{ __('findz/book.from_minimum_cost', ['cost' => number_format($stadium->getMinimumCourtCost(), 0, '.', '.') ]) }} uzs/{{ __('findz/book.per_hour') }}</span>
                        <span>{{ $stadium->address }}</span>
                    </div>
                </div>
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

            $('.scroll-wrapper').on('wheel', function(event) {
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
