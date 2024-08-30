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
    <header class="d-flex row justify-content-between align-items-center">
        <img id="lang-icon" src="{{ app()->getLocale() == 'uz' ? asset('img/findz/icons/uz_flag.svg') : asset('img/findz/icons/ru_flag.svg') }}" alt="lang icon">
        <h1 class="findz">FINDZ</h1>
        <a href="{{ route('findz.filter', $currentSportTypeId) }}">
            <img src="{{ asset('img/findz/icons/filter.svg') }}" alt="filter icon">
        </a>
    </header>
@endsection

@section('content')
    <nav>
        <div class="scroll-wrapper">
            <ul class="sport_types d-flex row align-items-center gap-12">
                @foreach($sportTypes as $sportType)
                    <li class="@if($sportType->id ==  $currentSportTypeId) nav_active @endif">
                        <a href="{{ route('findz.courts.filter.sport.type', ['sportType' => $sportType->id, 'date' => request('date'), 'start_time' => request('start_time'), 'end_time' => request('end_time')]) }}">{{ $sportType->name }}</a>
                    </li>
                @endforeach
            </ul>
        </div>
    </nav>

    <div class="select-lang">
        @if (app()->getLocale() == 'uz')
            <a href="{{ url('set-lang/uz') }}">
                <button class="selected-lang" >
                    Uz
                </button>
            </a>
            <a href="{{ url('set-lang/ru') }}">
                <button>
                    Ru
                </button>
            </a>
        @else
           <a href="{{ url('set-lang/ru') }}">
                    <button class="selected-lang" >
                        Ru
                    </button>
            </a>
            <a href="{{ url('set-lang/uz') }}">
                <button>
                    Uz
                </button>
            </a>
        @endif
    </div>


    @if(request('date') || request('start_time') || request('end_time'))
        <div class="date_time d-flex row align-items-center gap-12">
            @if(request('date'))
                <div class="date d-flex row align-items-center justify-content-center">
                    <p>{{ request('date') }}</p>
                    <a href="{{ route('findz.courts.filter.sport.type', array_merge(request()->query(), ['sportType' => $currentSportTypeId, 'date' => null, 'start_time' => request('start_time'), 'end_time' => request('end_time')])) }}"
                       class="pointer ml-10">
                        <img src="{{ asset('img/findz/icons/cancel.svg') }}" alt="cancel icon">
                    </a>
                </div>
            @endif
            @if(request('start_time') && request('end_time'))
                <div class="time d-flex row align-items-center justify-content-center">
                    <p>{{ request('start_time') }} - {{ request('end_time') }}</p>
                    <a href="{{ route('findz.courts.filter.sport.type', array_merge(request()->query(), ['sportType' => $currentSportTypeId, 'date' => request('date'), 'start_time' => null, 'end_time' => null])) }}"
                       class="pointer ml-10">
                        <img src="{{ asset('img/findz/icons/cancel.svg') }}" alt="cancel icon">
                    </a>
                </div>
            @endif
        </div>
    @endif


    <div class="container_mobile">
        <div class="content">
            @foreach($courts as $court)
                <div class="stadiums mt-30"
                     onclick="location.href='{{ route('findz.show.court', ['court' => $court->id, 'date' => request('date'), 'start_time' => request('start_time'), 'end_time' => request('end_time')]) }}'">
                    @if($court->photos)
                        <div class="court_images">
                            <div class="scroll-container">
                                @foreach(json_decode($court->photos) as $photo)
                                    <img src="{{\Illuminate\Support\Facades\Storage::url($photo)}}" alt="court photo"/>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    <div class="stadium_desc mt-15">
                        <p>{{ $court->name }}</p>
                        <span>{{ __('findz/book.from_minimum_cost', ['cost' => $court->getMinimumCost()]) }}.000 uzs/{{ __('findz/book.per_hour') }}</span>
                        <span>{{ $court->stadium->address }}</span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection

@section('extra-scripts')
    <script>
     $(document).ready(function() {
        $('.scroll-wrapper').on('wheel', function(event) {
            if (event.originalEvent.deltaY !== 0) {
                this.scrollLeft += event.originalEvent.deltaY;
                event.preventDefault();
            }
        });

        $('#lang-icon').on('click', function() {
            $('.select-lang').css('display', 'block');
            $('.container_mobile').css('margin', '152px auto 0', 'important');

            @if(request('date') || request('start_time') || request('end_time'))
                $('.date_time ').css('top', '136px');
                $('.container_mobile').css('margin', '202px  auto 0', 'important');
            @endif
        });

        $(window).on('click', function(event) {
            if (!$(event.target).closest('.select-lang, #lang-icon').length) {
                $('.select-lang').hide();
                $('.container_mobile').css('margin', '122px auto 0', 'important');

                 @if(request('date') || request('start_time') || request('end_time'))
                    $('.date_time ').css('top', '95px');
                    $('.container_mobile').css('margin', '182px auto 0', 'important');
                @endif
            }
        });
    });
    </script>
@endsection