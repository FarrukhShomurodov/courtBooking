@php
    use Carbon\Carbon;
@endphp

@extends('findz.layouts.app')

@section('extra-css')
    <link rel="stylesheet" href="{{ secure_asset('css/findz/filter.css') }}"/>
    <link rel="stylesheet" href="{{ secure_asset('/css/findz/book.css') }}"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@endsection

@section('header')
    <div class="dark-overlay"></div>
    <header class="d-flex row align-items-center justify-content-between fixed-header">
        <a href="{{ url()->previous() }}">
            <img src="{{ asset('img/findz/icons/back.svg') }}" alt="back icon" class="header-icon">
        </a>
    </header>
@endsection

@section('content')
    <div class="container_mobile">

        <div class="date-selector">
            <button class="prev">
                <img src="{{ asset('img/findz/icons/prev.svg') }}" alt="prev icon" class="header-icon">
            </button>
            <span class="date d-flex align-items-center" id="calendar_date"></span>
            <button class="next">
                <img src="{{ asset('img/findz/icons/next.svg') }}" alt="next icon" class="header-icon">
            </button>
        </div>

        <div class="time-slots">
            <div class="court_stadium"> {{ $courts->first()->stadium->name }}</div>
            <table class="slots-table">
                <thead>
                <tr>
                    @foreach($courts as $court)
                        <th data-court-id="{{ $court->id }}">{{ $court->name }}</th>
                    @endforeach
                </tr>
                </thead>

                <tbody class="table-border-bottom-0">
                <tr>
                    @foreach($courts as $court)
                        <td style="padding: 0px !important;" data-court-id="{{$court->id}}">
                            @foreach($court->schedules as $schedule)
                                @php
                                    $bookingId = 0;
                                    $hasBooking = false;
                                    $currentTime = Carbon::parse($schedule->start_time);

                                    $date = Carbon::parse(request('date'));


                                    foreach($court->bookings as $booking) {
                                        $bookingId = $booking->id;

                                        $bookingDate =  Carbon::parse($booking->date);
                                        $hasDate = ($date == $bookingDate) ?? $bookingDate->isToday();

                                        if ($hasDate) {
                                            $bookingStartTime = Carbon::parse($booking->start_time);
                                            $bookingEndTime = Carbon::parse($booking->end_time);
                                            if ($currentTime->between($bookingStartTime, $bookingEndTime)) {
                                                $hasBooking = true;
                                                break;
                                            }
                                        }
                                    }
                                @endphp
                                @php
                                    $selectedCourt = $selectedCourt ?? request('court');
                                    $selectedStartTime = $selectedStartTime ?? request('start_time');
                                    $selectedEndTime = $selectedEndTime ?? request('end_time');
                                @endphp

                                <div
                                    class="slot @if($hasBooking) slot_booked @endif @if($court->id == $selectedCourt && (substr($schedule->start_time, 0, 5) == $selectedStartTime || substr($schedule->start_time, 0, 5) == $selectedEndTime)) selected @endif"
                                    data-time="{{ substr($schedule->start_time, 0, 5) }}"
                                    data-field="{{ $court->name }}"
                                    data-price="{{ $schedule->cost }}"
                                    data-court-id="{{$court->id}}"
                                    data-booking-id="{{$bookingId}}">{{ substr($schedule->start_time, 0, 5) }}<br>
                                    <span>{{ $schedule->cost }} т.с/ч</span>
                                </div>

                            @endforeach

                        </td>
                    @endforeach
                </tr>
                </tbody>
            </table>
        </div>

        <div class="selected-slots">
        </div>

        <div class="total">
            <h1>{{ __('findz/book.Итого:') }}</h1>
            <h1 class="total-price">0 {{ __('findz/book.т.с') }}</h1>
        </div>

        @if(!$isUpdate)
            <div class="payment-method mt-30">
                <h3>{{ __('findz/book.Способ Оплаты') }}</h3>
                <div class="payment-options">
                    <button class="active-payment" data-payment="payme">{{ __('PayMe') }}</button>
                    <button data-payment="uzum">{{ __('Uzum') }}</button>
                </div>
            </div>
        @endif

        <div class="booking-rules mt-30">
            <p>{{ __('findz/book.Правила брони') }}</p>
            <ul>
                {{--                <li>{{ __('findz/book.Резервация может быть отменена с возвратом средств, если до ее начала остается более чем 24 часа. В противном случае, средства не возвращаются') }}</li>--}}
                <li>{{ __('findz/book.Рекомендуется быть на месте за 15 минут до начала') }}</li>
            </ul>
        </div>

        <div class="error_modal" id="error_modal">
            <div class="d-flex justify-content-between align-items-center">
                <span class="res_error"></span>
                <img src="{{ asset('img/findz/icons/close.svg') }}" alt="close btn"/>
            </div>
        </div>
    </div>

@endsection

@section('footer')
    <footer class="w-100 d-flex justify-content-around row">
        <button id="close-btn"
                class="nav_active btn footer_btn book">{{$isUpdate ? 'Готово' : __('findz/book.Оплатить через PayMe') }}</button>
    </footer>
@endsection

@section('extra-scripts')
    <script>
        $(document).ready(function () {
            let selectedDate = @json(request()->input('date') ?? date('Y-m-d'));
            @if($isUpdate)
                selectedDate = @json($userBook->date);
            @endif
            let dateObject = new Date(selectedDate);
            let dayOfWeek = getRussianDayOfWeek(dateObject);

            flatpickr.localize(flatpickr.l10ns.ru);
            let flatpickrCalendar = flatpickr("#calendar_date", {
                static: true,
                disableMobile: true,
                minDate: "today",
                monthSelectorType: "static",
                defaultDate: selectedDate,
                onOpen: function () {
                    $('.dark-overlay').addClass('show-overlay');
                    $('html, body').css({
                        overflow: 'hidden',
                        height: '100%'
                    });
                },
                onClose: function (selectedDates, dateStr) {
                    $('.dark-overlay').removeClass('show-overlay');
                    $('html, body').css({
                        overflow: 'auto',
                        height: 'auto'
                    });
                    const selected_date = selectedDates[0];
                    const dayOfWeek = getRussianDayOfWeek(selected_date);
                    $('.date').html(`${dateStr}, ${dayOfWeek}`);
                },
                onChange: function (selectedDates, dateStr) {
                    selectedDate = dateStr
                }
            });

            updateDateDisplay();

            $('.prev').click(function () {
                dateObject.setDate(dateObject.getDate() - 1);
                selectedDate = formatDate(dateObject);
                updateDateDisplay();
            });

            $('.next').click(function () {
                dateObject.setDate(dateObject.getDate() + 1);
                selectedDate = formatDate(dateObject);
                updateDateDisplay();
            });

            function updateDateDisplay() {
                dayOfWeek = getRussianDayOfWeek(dateObject);
                $('.date').html(`${selectedDate}, ${dayOfWeek}`);
                flatpickrCalendar.setDate(dateObject, true);

                const url = new URL(window.location.href);
                url.searchParams.set('date', selectedDate);
                window.history.replaceState(null, null, url);

                //Slots update
                $.ajax({
                    url: '/api/get-schedule',
                    method: 'GET',
                    data: {date: selectedDate},
                    success: function (res) {
                        updateSlots(res);
                    },
                    error: function (err) {
                        console.error("Error fetching schedule: ", err);
                    }
                });
            }

            function formatDate(date) {
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                return `${year}-${month}-${day}`;
            }

            function getRussianDayOfWeek(date) {
                @if ( app()->getLocale() == 'ru')
                const daysOfWeek = ['Воскресенье', 'Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота'];
                @else
                const daysOfWeek = ['Yakshanba', 'Dushanba', 'Seshanba', 'Chorshanba', 'Payshanba', 'Juma', 'Shanba'];
                @endif

                    return daysOfWeek[date.getDay()];
            }

            function updateSlots(data) {
                $('.slots-table tbody tr').empty();
                data.courts.forEach(court => {
                    let row = `<td style="padding: 0px !important;" data-court-id="${court.id}">`;
                    court.schedules.forEach(schedule => {

                        let hasBooking = false

                        @if($isUpdate)
                        let oldSelectedSlot = (court.id === {{$userBook->court_id}} && schedule.start_time >= `{{$userBook->start_time}}` && schedule.start_time <= `{{$userBook->end_time}}`);
                        hasBooking = false;
                        @else
                            hasBooking = data.bookings.some(booking => {
                            let bookingDate = new Date(booking.date).toISOString().slice(0, 10);
                            return court.id == booking.court_id && bookingDate === selectedDate && schedule.start_time >= booking.start_time && schedule.start_time <= booking.end_time;
                        });
                        @endif


                            @if($isUpdate)
                            selected = oldSelectedSlot ? 'selected' : '';
                        @else
                        let selected = (court.id == {{ request('court') }} && (schedule.start_time.slice(0, 5) == `{{ $selectedStartTime }}` || schedule.start_time.slice(0, 5) == `{{ $selectedEndTime }}`));
                        @endif

                        let selectedClass = selected ? 'selected' : '';

                        let slotClass = hasBooking ? 'slot_booked' : '';

                        row += `
                            <div class="slot ${slotClass} ${selectedClass}"
                                data-time="${schedule.start_time.slice(0, 5)}"
                                data-field="${court.name}"
                                data-price="${schedule.cost}"
                                data-court-id="${court.id}">
                                ${schedule.start_time.slice(0, 5)}<br>
                                <span>${schedule.cost} т.с/ч</span>
                            </div>
                        `;
                    });
                    row += '</td>';
                    $('.slots-table tbody tr').append(row);
                });

                @if (request('start_time'))
                $('.slot.selected').each(function () {
                    const field = $(this).data('field');
                    const time = $(this).data('time');

                    initialSelectedSlots = $('.slot.selected');
                    previousSelectedSlot = initialSelectedSlots.first();
                    autoSelectSlotsInRange(previousSelectedSlot, $(this));
                });
                @endif
            }


            function autoSelectNextSlot(currentSlot) {
                const nextTime = getNextTime(currentSlot.data('time'));
                const nextSlot = currentSlot.siblings(`.slot[data-time="${nextTime}"]`);

                if (nextSlot.length && !nextSlot.hasClass('slot_booked') && !nextSlot.hasClass('selected')) {
                    nextSlot.addClass('selected');
                }
            }

            function getNextTime(time) {
                const [hour, minute] = time.split(':').map(Number);
                const nextHour = hour + 1;
                return `${nextHour < 10 ? '0' : ''}${nextHour}:${minute < 10 ? '0' : ''}${minute}`;
            }

            function autoSelectSlotsInRange(startSlot, endSlot) {
                const startTime = startSlot.data('time');
                const endTime = endSlot.data('time');
                const courtName = startSlot.data('field');
                const isSelectingBackward = new Date(`1970-01-01T${startTime}:00Z`) > new Date(`1970-01-01T${endTime}:00Z`);

                let selecting = false;

                startSlot.siblings('.slot').addBack().each(function () {
                    const slotTime = $(this).data('time');
                    const slotCourtName = $(this).data('field');

                    if (slotCourtName === courtName) {
                        if (!isSelectingBackward && slotTime === startTime || isSelectingBackward && slotTime === endTime) {
                            selecting = true;
                        }

                        if (selecting && !$(this).hasClass('slot_booked') && !$(this).hasClass('selected')) {
                            $(this).addClass('selected');
                        }

                        if (!isSelectingBackward && slotTime === endTime || isSelectingBackward && slotTime === startTime) {
                            selecting = false;
                        }
                    }
                });
            }


            let isFirstSlot = false;

            function updateSelectedSlots() {
                const selectedSlots = [];

                $('.slot.selected').each(function () {
                    selectedSlots.push({
                        field: $(this).data('field'),
                        court_id: $(this).data('court-id'),
                        time: $(this).data('time'),
                        price: parseFloat($(this).data('price')),
                    });
                });

                const groupedSlots = selectedSlots.reduce((groups, slot) => {
                    if (!groups[slot.field]) {
                        groups[slot.field] = [];
                    }
                    groups[slot.field].push(slot);
                    return groups;
                }, {});

                $('.selected-slots').empty();
                let total = 0;

                for (const field in groupedSlots) {
                    const fieldSlots = groupedSlots[field];

                    fieldSlots.sort((a, b) => a.time.localeCompare(b.time));

                    let combinedSlots = [];
                    let currentSlot = null;

                    fieldSlots.forEach((slot, index) => {
                        if (!currentSlot) {
                            currentSlot = {
                                start: slot.time,
                                court_id: slot.court_id,
                                end: isFirstSlot ? getNextTime(slot.time) : slot.time,
                                price: slot.price
                            };
                        } else if (new Date(`1970-01-01T${currentSlot.end}:00Z`) >= new Date(`1970-01-01T${slot.time}:00Z`)) {
                            currentSlot.end = getNextTime(slot.time);
                            currentSlot.price += slot.price;
                        } else {
                            combinedSlots.push(currentSlot);
                            currentSlot = {
                                start: slot.time,
                                court_id: slot.court_id,
                                end: isFirstSlot ? getNextTime(slot.time) : slot.time,
                                price: slot.price
                            };
                        }
                    });

                    if (currentSlot) {
                        combinedSlots.push(currentSlot);
                    }


                    if (combinedSlots.length > 1) {
                        let result = combinedSlots.reduce((acc, slot) => {
                            acc.start = acc.start ? (acc.start < slot.start ? acc.start : slot.start) : slot.start;
                            acc.end = acc.end ? (acc.end > slot.end ? acc.end : slot.end) : slot.end;
                            acc.price += slot.price;
                            acc.court_id = slot.court_id;
                            return acc;
                        }, {start: null, end: null, price: 0});

                        const slotDiv = $(`
                <div class="selected-slot">
                    <div>
                        <h2  data-field="${field}" data-court-id="${result.court_id}">${field}</h2>
                        <div>
                            <span>
                                <span data-start-time="${result.start}"> ${result.start} </span>
                                -
                                <span data-end-time="${result.end}"> ${result.end} </span>
                                </span>
                            <span class="selected_date">${selectedDate}, ${dayOfWeek}</span>
                        </div>
                    </div>
                    <div class="cost_cancel_section">
                        <h2>${result.price} т.с</h2>
                        <button class="delete-btn" data-field="${field}" data-start="${result.start}" data-end="${result.end}">
                            <img src="../../../img/findz/icons/delete_selected_time.svg" alt="delete selected time icon">
                        </button>
                    </div>
                </div>
            `);

                        total += result.price;
                        $('.selected-slots').append(slotDiv);
                    }

                    $('.total-price').text(`${total} т.с`);

                    $('.selected-slots').off('click', '.delete-btn').on('click', '.delete-btn', function () {
                        const field = $(this).data('field');
                        const start = $(this).data('start');
                        const end = $(this).data('end');

                        $('.slot.selected').each(function () {
                            if ($(this).data('field') === field && ($(this).data('time') >= start || $(this).data('time') < end)) {
                                $(this).removeClass('selected');
                                previousSelectedSlot = null;
                            }
                        });

                        updateSelectedSlots();
                    });
                }
            }

            @if($isUpdate)
            setTimeout(() => updateSelectedSlots(), 1000);
            @endif

            let previousSelectedSlot = null;

            $(document).on('click', '.slot', function () {
                const isSelected = $(this).hasClass('selected');
                const isBooked = $(this).hasClass('slot_booked');
                const isFirstSlot = $(this).siblings('.selected').length === 0;

                if (!isBooked) {
                    $(this).toggleClass('selected');

                    if (!isSelected) {
                        if (previousSelectedSlot && previousSelectedSlot.data('field') === $(this).data('field')) {
                            autoSelectSlotsInRange(previousSelectedSlot, $(this));
                        } else {
                            previousSelectedSlot = $(this);
                        }
                    } else {
                        previousSelectedSlot = null;
                    }

                    // Auto-select next slot if it's the first selected slot
                    if (!isSelected && isFirstSlot) {
                        autoSelectNextSlot($(this));
                    }

                    updateSelectedSlots();
                }
            });

            updateSelectedSlots();

            $('.payment-options button').click(function () {
                const paymentMethod = $(this).data('payment');

                $('.payment-options button').removeClass('active-payment');
                $(this).addClass('active-payment');

                @if ( app()->getLocale() == 'ru')
                $('#close-btn').text(`Оплатить через ${paymentMethod.charAt(0).toUpperCase() + paymentMethod.slice(1)}`);
                @else
                $('#close-btn').text(`${paymentMethod.charAt(0).toUpperCase() + paymentMethod.slice(1)} orqali to\'lash`);
                @endif
            });

            $(document).on('click', '.book', function () {
                const selectedSlots = [];
                $('.selected-slots .selected-slot').each(function () {
                    selectedSlots.push({
                        court_id: $(this).find('h2').data('court-id'),
                        start_time: $(this).find('span[data-start-time]').data('start-time'),
                        end_time: $(this).find('span[data-end-time]').data('end-time'),
                        price: parseInt($(this).find('.cost_cancel_section h2').text().replace('т.с', '').trim(), 10)
                    });
                });

                let tg = window.Telegram.WebApp;
                let userData = tg.initDataUnsafe;
                let chat_id = userData.user.id;

                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

                $.ajax({
                    url: `/api/bot-user/${chat_id}`,
                    method: 'GET',
                    success: function (response) {
                        if (response.success) {
                            const user = response.data;

                            const bookingData = {
                                user_id: user.id,
                                full_name: `${user.first_name} ${user.second_name || ''}`.trim(),
                                phone_number: user.phone,
                                slots: selectedSlots,
                                date: selectedDate,
                                source: 'bot'
                            };

                            @if($isUpdate)
                                bookingData.date = @json($userBook->date);

                            console.log(bookingData)
                            $.ajax({
                                url: '/api/booking/{{$userBook->id}}',
                                method: 'PUT',
                                data: bookingData,
                                success: function (response) {
                                    window.location.href = '{{ route('findz.mybookings', ['sportType' => $currentSportTypeId]) }}';
                                },
                                error: function (err) {
                                    let errors = err.responseJSON.message;
                                    let errorHtml = `<div class="alert alert-solid-danger" role="alert"><li>${errors}</li></div>`;
                                    $('.res_error').empty();
                                    $('.res_error').append(errorHtml);
                                    $('#error_modal').fadeIn().delay(5000).fadeOut();
                                }
                            });
                            @else
                            $.ajax({
                                url: '/api/booking',
                                method: 'POST',
                                data: bookingData,
                                success: function (response) {
                                    if ({{$isUpdate}}) {
                                        initiatePaycomPayment(response.booking_id, bookingData.price);
                                    } else {
                                        window.location.href = '{{ route('findz.mybookings', ['sportType' => $currentSportTypeId]) }}';
                                    }
                                },
                                error: function (err) {
                                    let errors = err.responseJSON.message;
                                    let errorHtml = `<div class="alert alert-solid-danger" role="alert"><li>${errors}</li></div>`;
                                    $('.res_error').empty();
                                    $('.res_error').append(errorHtml);
                                    $('#error_modal').fadeIn().delay(5000).fadeOut();
                                }
                            });
                            @endif



                        } else {
                            console.log('Ошибка: пользователь не найден');
                        }
                    },
                    error: function (error) {
                        console.log('Ошибка при получении данных пользователя', error);
                    }
                });

                function initiatePaycomPayment(bookingId, amount) {
                    // Здесь создаем и отправляем форму на Paycom
                    let paycomForm = `
                        <form id="form-payme" method="POST" action="https://checkout.paycom.uz/">
                            <input type="hidden" name="merchant" value="66cdfb052f8d5ff4746f8435">
                            <input type="hidden" name="account[book_id]" value="${bookingId}">
                            <input type="hidden" name="amount" value="${amount}">
                            <input type="hidden" name="lang" value="{{app()->getLocale()}}">
                            <input type="hidden" name="button" data-type="svg" value="colored">
                        </form>
                    `;

                    $('body').append(paycomForm);
                    $('#form-payme').submit();
                }

                $('#error_modal img').click(function () {
                    $('.error_modal').hide();
                });

            });
        });

    </script>
@endsection
