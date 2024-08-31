@extends('findz.layouts.app')

@section('extra-css')
    <link rel="stylesheet" href="{{ asset('css/findz/filter.css') }}"/>
    <link rel="stylesheet" href="{{ asset('css/findz/show.css') }}"/>
@endsection

@section('header')
    <header class="d-flex row align-items-center justify-content-between fixed-header">
        <a href="{{ route('webapp', ['sportType' => $court->sportTypes->id, 'date' => request('date'), 'start_time' => request('start_time'), 'end_time' => request('end_time')]) }}">
            <img src="{{ asset('img/findz/icons/back.svg') }}" alt="back icon" class="header-icon">
        </a>
        <img id="favourite" src="{{ asset('img/findz/icons/favourite.svg') }}" alt="favourite icon" class="header-icon">
    </header>
@endsection

@section('content')
    <div class="container_mobile">
        <div class="content">
            <div class="stadiums">
                @if($court->photos)
                    <div class="court_images">
                        <div class="scroll-container">
                            @foreach(json_decode($court->photos) as $photo)
                                <img src="{{ \Illuminate\Support\Facades\Storage::url($photo) }}" alt="Stadium photos"/>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="date_time d-flex row align-items-center gap-12">
                    <div class="d-flex row align-items-center justify-content-center ">
                        <p>{{ $court->sportTypes->name }}</p>
                    </div>
                    <div class="d-flex row align-items-center justify-content-center">
                        <p>от {{ $court->getMinimumCost() }}.000 uzs/час</p>
                    </div>
                </div>

                <div class="stadium_desc mt-15">
                    <h1>{{ $court->name }}</h1>
                    <span id="description">{{ $court->description }}</span>
                    <p class="pointer mt-15" id="read-more">Читать полностью</p>
                    <div class="w-100 mt-30">
                        <div class="d-flex justify-content-between align-items-center address">
                            <h2 id="address-text">{{ $court->stadium->address }}</h2>
                            <img src="{{ asset('img/findz/icons/copy.svg') }}" alt="copy icon" id="copy-icon"
                                 style="cursor: pointer;">
                        </div>
                        <a href="{{ $court->stadium->map_link }}">
                            <img class="yamap mt-15"
                                 src="https://static-maps.yandex.ru/v1?lang=en_US&ll=28.97709,41.005233&z=14&theme=dark&apikey=dbbcb516-093e-4f66-9fcf-3a152aa0d7bd"
                                 style="margin: 0; border-radius: 20px; width: 100%; height: 100vh;">
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="copy-modal" id="copy-modal">
            <div class="d-flex justify-content-between align-items-center">
            <span>{{ __('findz/book.address_copied') }}</span>
            <img src="{{ asset('img/findz/icons/close.svg') }}" alt="close btn"/>
        </div>
    </div>

    <div class="favourite_modal" id="favourite-modal">
        <div class="d-flex justify-content-between align-items-center">
            <span>{{ __('findz/book.added_to_favorites') }}</span>
            <img src="{{ asset('img/findz/icons/close.svg') }}" alt="close btn"/>
        </div>
    </div>
@endsection

@section('footer')
    <footer class="w-100 d-flex justify-content-around row">
        <button id="close-btn" class="nav_active btn footer_btn"
                onclick="location.href='{{ route('findz.book', ['court' => $court->id, 'date' => request('date'), 'start_time' => request('start_time'), 'end_time' => request('end_time')]) }}'">
            {{ __('findz/book.book_now') }}
        </button>
    </footer>
@endsection

@section('extra-scripts')
    <script>
        $(document).ready(function () {
            LlfromAddress('{{$court->stadium->address}}')

            function truncateText(selector, maxChars) {
                var element = $(selector);
                var originalText = element.text();
                if (originalText.length > maxChars) {
                    var truncatedText = originalText.slice(0, maxChars) + '...';
                    element.data('original-text', originalText);
                    element.data('truncated-text', truncatedText);
                    element.text(truncatedText);
                }
            }

            $('#read-more').click(function () {
                var description = $('#description');
                if (description.hasClass('expanded')) {
                    description.text(description.data('truncated-text'));
                    $(this).text('Читать полностью');
                } else {
                    description.text(description.data('original-text'));
                    $(this).text('Свернуть');
                }
                description.toggleClass('expanded');
            });

            truncateText('#description', 300);

            $('#copy-icon').click(function () {
                var addressText = $('#address-text').text();
                var $temp = $('<textarea>');
                $('body').append($temp);
                $temp.val(addressText).select();
                document.execCommand('copy');
                $temp.remove();
                $('#copy-modal').fadeIn().delay(2000).fadeOut();
            });

            $('.copy-modal img').click(function () {
                $('.copy-modal').hide();
            })

            $('#favourite').click(function () {
                if ($(this).attr('src') === '{{ asset('img/findz/icons/active_favourite.svg') }}') {
                    $(this).attr('src', '{{ asset('img/findz/icons/favourite.svg') }}');
                } else {
                    $(this).attr('src', '{{ asset('img/findz/icons/active_favourite.svg') }}');
                    $('#favourite-modal').fadeIn().delay(2000).fadeOut();
                }
            });

            $('.favourite_modal img').click(function () {
                $('.favourite_modal').hide();
            });


            function LlfromAddress(address) {
                $.ajax({
                    url: `https://geocode-maps.yandex.ru/1.x/?apikey=c5d4f382-8c0d-42e0-ab27-871a632d7bd8&geocode=${address}&format=json`,
                    method: 'Get',
                    success: function (response) {
                        let points = response.response.GeoObjectCollection.featureMember[0].GeoObject.Point.pos
                        points = points.replace(' ', ',')
                        $('.yamap').attr('src', `https://static-maps.yandex.ru/v1?lang=en_US&ll=${points}&z=14&theme=dark&apikey=dbbcb516-093e-4f66-9fcf-3a152aa0d7bd`)
                    },
                    error: function (err) {
                        console.log(err);
                    }
                });
            }
        });
    </script>
@endsection
