@extends('findz.layouts.app')

@section('extra-css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="{{secure_asset('css/findz/filter.css')}}"/>
@endsection

@section('header')
    <header class="d-flex row align-items-center fixed-header">
        <a href="{{ route('webapp') }}">
            <img src="{{ asset('img/findz/icons/back.svg') }}" alt="{{ __('findz/book.lang_icon') }}" class="header-icon">
        </a>
        <p class="header-text">{{ __('findz/book.date_time') }}</p>
    </header>
@endsection

@section('content')
    <div class="container_mobile">
        <div id="calendar_date" class="mt-25"></div>
        <button class="btn_select_time d-flex align-items-center justify-content-center mt-25">
            {{ __('findz/book.select_time') }}
            <img src="{{ asset('img/findz/icons/select_time.svg') }}" alt="{{ __('findz/book.select_time_icon') }}">
        </button>

        <div class="time-picker">
            <div class="time-picker-column" id="startPicker">
            </div>
            <div class="divider"></div>
            <div class="time-picker-column" id="endPicker">
            </div>
        </div>
    </div>
@endsection


@section('footer')
    <footer class="w-100 d-flex justify-content-around row">
        <button class="nav_active footer_btn  btn reset_btn">Сбросить</button>
        @php
            $previousUrl = url()->previous(); // Get the previous URL
            $parsedUrl = parse_url($previousUrl); // Parse the URL

            // Extract query parameters
            parse_str($parsedUrl['query'] ?? '', $queryParams);

            // Merge new query parameters
            $queryParams = array_merge($queryParams, ['sportType' => $currentSportTypeId]);

            // Build the updated query string
            $updatedQueryString = http_build_query($queryParams);

            // Construct the new URL with updated query parameters
            $actionUrl = $parsedUrl['path'] . ($updatedQueryString ? '?' . $updatedQueryString : '');
        @endphp


        <form method="GET"
              action="{{ $actionUrl }}">
            <input type="hidden" name="date" value="{{ request('date') }}">
            <input type="hidden" id="start_time" name="start_time" value="{{ request('start_time') }}">
            <input type="hidden" id="end_time" name="end_time" value="{{ request('end_time') }}">
            <button class="nav_active footer_btn btn">Сохранить</button>
        </form>
    </footer>
@endsection

@section('extra-scripts')
    <script>
        flatpickr.localize(flatpickr.l10ns.ru);

        $(document).ready(function () {
            let date = flatpickr("#calendar_date", {
                inline: true,
                static: true,
                disableMobile: true,
                minDate: "today",
                monthSelectorType: "static",
                onChange: function (selectedDates, dateStr, instance) {
                    $('input[name="date"]').val(dateStr);
                }
            });

            const $btnSelectTime = $('.btn_select_time');
            const $timePicker = $('.time-picker');
            const $resetBtn = $('.reset_btn');
            const $navActive = $('.nav_active');

            $btnSelectTime.on('click', () => {
                const isTimePickerVisible = $timePicker.css('display') === 'flex';

                if (isTimePickerVisible) {
                    $btnSelectTime.html(`Выбрать Время <img src="{{ asset('img/findz/icons/select_time.svg') }}" alt="select time icon">`);
                    $timePicker.css('display', 'none');
                    $resetBtn.css('display', 'none');
                    $navActive.css('width', '342px');
                } else {
                    $btnSelectTime.html(`Только Дата <img src="{{ asset('img/findz/icons/unselect_time.svg') }}" alt="select time icon">`);
                    $timePicker.css('display', 'flex');
                    $resetBtn.css('display', 'block');
                    $navActive.css('width', '165px');
                    $('#start_time').val('');
                    $('#end_time').val('');
                    initializeTimePickers();
                }
            });

            function initializeTimePickers() {
                const timeOptions = [
                    "", "", "", "", "00:00", "01:00", "02:00", "03:00", "04:00", "05:00", "06:00",
                    "07:00", "08:00", "09:00", "10:00", "11:00", "12:00", "13:00",
                    "14:00", "15:00", "16:00", "17:00", "18:00", "19:00", "20:00",
                    "21:00", "22:00", "23:00", "", "", "", ""
                ];

                function addTimeOptions(picker) {
                    timeOptions.forEach(time => {
                        if (time === '02:00') {
                            picker.append(`<button class="time-option active" data-time="${time}">${time}</button>`);
                        } else {
                            picker.append(`<button class="time-option" data-time="${time}">${time}</button>`);
                        }
                    });
                }

                function activateTimePicker(selector, inputId) {
                    const picker = $(selector);
                    const optionHeight = 37;

                    addTimeOptions(picker);

                    picker.on('click', '.time-option', function () {
                        picker.find('.time-option').removeClass('active');
                        $(this).addClass('active');
                        centerActiveOption(picker);

                        // Обновляем значение скрытого input и выводим его в консоль
                        const selectedTime = $(this).data('time');
                        $(`#${inputId}`).val(selectedTime);
                        console.log(`${inputId}: ${selectedTime}`);
                    });

                    picker.on('wheel', function (event) {
                        event.preventDefault();
                        const scrollAmount = optionHeight * (event.originalEvent.deltaY > 0 ? 1 : -1);
                        const newScrollTop = Math.max(0, Math.min(picker.scrollTop() + scrollAmount, picker[0].scrollHeight - picker.height()));
                        picker.scrollTop(newScrollTop);
                    });

                    picker.on('scroll', function () {
                        let scrollTop = picker.scrollTop();
                        let scrollHeight = picker[0].scrollHeight;
                        let pickerHeight = picker.height();

                        let closest = null;
                        let closestDist = Number.MAX_VALUE;
                        const pickerCenter = scrollTop + pickerHeight / 2;

                        picker.find('.time-option').each(function () {
                            const optionCenter = $(this).position().top + scrollTop + $(this).outerHeight() / 2;
                            const dist = Math.abs(optionCenter - pickerCenter);

                            if (dist < closestDist) {
                                closest = $(this);
                                closestDist = dist;
                            }
                        });

                        picker.find('.time-option').removeClass('active');
                        if (closest) {
                            closest.addClass('active');
                        }

                        const activeOption = picker.find('.time-option.active');
                        if (activeOption.length) {
                            const optionCenter = activeOption.position().top + picker.scrollTop() + activeOption.outerHeight() / 2;
                            const targetScrollTop = optionCenter - picker.height() / 2;
                            picker.scrollTop(targetScrollTop);
                        }

                        const selectedTime = activeOption.text();
                        $(`#${inputId}`).val(selectedTime);
                    });

                    function centerActiveOption(picker) {
                        const activeOption = picker.find('.time-option.active');
                        if (activeOption.length) {
                            const optionCenter = activeOption.position().top + picker.scrollTop() + activeOption.outerHeight() / 2;
                            const targetScrollTop = optionCenter - picker.height() / 2;
                            picker.scrollTop(targetScrollTop);
                        }
                    }

                    centerActiveOption(picker);

                    $resetBtn.on('click', () => {
                        $('#startPicker').find('.time-option').removeClass('active');
                        $('#endPicker').find('.time-option').removeClass('active');
                        $('#start_time').val('02:00');
                        $('#end_time').val('02:00');
                        $('#startPicker .time-option[data-time="02:00"]').addClass('active');
                        $('#endPicker .time-option[data-time="02:00"]').addClass('active');
                        date.clear()

                        centerActiveOption(picker);
                    });
                }

                activateTimePicker('#startPicker', 'start_time');
                activateTimePicker('#endPicker', 'end_time');
            }
        });
    </script>
@endsection
