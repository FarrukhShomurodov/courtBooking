@php
    use Carbon\Carbon;
@endphp
@php
    $selectedCourt = $selectedCourt ?? request('stadium');
    $selectedStartTime = $selectedStartTime ?? request('start_time');
    $selectedEndTime = $selectedEndTime ?? request('end_time');
@endphp

@extends('findz.layouts.app')

@section('extra-css')
    <link rel="stylesheet" href="{{ asset('css/findz/filter.css') }}"/>
    <link rel="stylesheet" href="{{ asset('/css/findz/book.css') }}"/>
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
            <div class="court_stadium"> {{ $stadium->name }}</div>
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
                class="nav_active btn footer_btn book disabled">{{$isUpdate ? 'Готово' : __('findz/book.Оплатить через PayMe') }}</button>
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

            let currentMonth = dateObject.getMonth();
            let currentYear = dateObject.getFullYear();
            let lastDayOfMonth = new Date(currentYear, currentMonth + 1, 0);

            flatpickr.localize(flatpickr.l10ns.ru);
            let flatpickrCalendar = flatpickr("#calendar_date", {
                static: true,
                disableMobile: true,
                minDate: "today",
                maxDate: lastDayOfMonth,
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
                    $('.selected_date').html(`${dateStr}, ${dayOfWeek}`);
                    dateObject =  new Date(dateStr);
                    const url = new URL(window.location.href);
                    url.searchParams.set('date', dateStr);
                    window.history.replaceState(null, null, url);
                },
                onChange: function (selectedDates, dateStr) {
                 
                },
            });


            $('.prev').click(function () {
                dateObject.setDate(dateObject.getDate() - 1);
                selectedDate = formatDate(dateObject);
                updateButtonState();
            });

            $('.next').click(function () {
                dateObject.setDate(dateObject.getDate() + 1);
                selectedDate = formatDate(dateObject);

                updateButtonState();
            });

            function updateButtonState() {
                let today = new Date();

                if (dateObject <= today) {
                    $('.prev').addClass('disabled');
                } else {
                    $('.prev').removeClass('disabled');
                }

                if (dateObject.getDate() >= lastDayOfMonth.getDate()) {
                    $('.next').addClass('disabled');
                } else {
                    $('.next').removeClass('disabled');
                }

                updateDateDisplay();
            }

            updateButtonState()

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
                    data: {
                        date: selectedDate,
                        stadium: {{ request('stadium') }},
                        sportTypeId: {{ $currentSportTypeId }}
                    },
                    success: function (res) {
                        const selectedSlots = [];
                        $('.selected-slots .selected-slot').each(function () {
                            selectedSlots.push({
                                court_id: $(this).find('h2').data('court-id'),
                                start_time: $(this).find('span[data-start-time]').data('start-time'),
                                end_time: $(this).find('span[data-end-time]').data('end-time'),
                                price: parseInt($(this).find('.cost_cancel_section h2').text().replace('т.с', '').trim(), 10)
                            });
                        });

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
                        let hasBooking = false;

                        @if($isUpdate)
                        let oldSelectedSlot = (court.id === {{ $userBook->court_id }} && schedule.start_time >= "{{ $userBook->start_time }}" && schedule.start_time <= "{{ $userBook->end_time }}");
                        hasBooking = false;
                        let endTime = "{{ $userBook->end_time }}"
                        endTime = endTime.slice(0, 5)
                        @else
                            hasBooking = data.bookings.some(booking => {
                            let bookingDate = new Date(booking.date).toISOString().slice(0, 10);
                            return court.id == booking.court_id && bookingDate === selectedDate && schedule.start_time >= booking.start_time && schedule.start_time <= booking.end_time;
                        });
                        @endif

                        let selected = false;

                        @if($isUpdate)
                            selected = oldSelectedSlot;
                        @else
                            selected = (court.id == {{ request('stadium') }} && (schedule.start_time.slice(0, 5) >= "{{ $selectedStartTime }}" && schedule.start_time.slice(0, 5) <= "{{ $selectedEndTime }}"));
                        let endTime = "{{ $selectedEndTime }}"
                        @endif

                        let selectedClass = selected ? 'selected' : '';
                        let slotClass = hasBooking ? 'slot_booked' : '';

                        if (schedule.start_time.slice(0, 5) == endTime) {
                            console.log("=-")
                            row += `
                            <div class="slot next_slot"
                                data-time="${schedule.start_time.slice(0, 5)}"
                                data-field="${court.name}"
                                data-price="${schedule.cost}"
                                data-court-id="${court.id}">
                                ${schedule.start_time.slice(0, 5)}<br>
                                <span>${schedule.cost} т.с/ч</span>
                            </div>
                        `;
                        } else {
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
                        }


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

                restoreSlotsFromLocalStorage()
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

            function updateSelectedSlots(restored = false) {
                const selectedSlots = [];

                $('.slot.selected').each(function () {
                    selectedSlots.push({
                        field: $(this).data('field'),
                        court_id: $(this).data('court-id'),
                        time: $(this).data('time'),
                        price: parseFloat($(this).data('price')),
                    });

                });

                $('.slot.next_slot').each(function () {
                    selectedSlots.push({
                        field: $(this).data('field'),
                        court_id: $(this).data('court-id'),
                        time: $(this).data('time'),
                        price: 0,
                    });
                });


                if (selectedSlots.length > 1 && !restored) {
                    saveSlotsToLocalStorage(selectedSlots);
                }

                if (selectedSlots.length >= 1) {
                    $('.book').removeClass('disabled');
                } else {
                    $('.book').addClass('disabled');
                }

                const groupedSlots = selectedSlots.reduce((groups, slot) => {
                    if (!groups[slot.field]) {
                        groups[slot.field] = [];
                    }
                    groups[slot.field].push(slot);
                    return groups;
                }, {});

                $('.selected-slots').empty();
                let total = 0;
                $('.total-price').text(`${total} т.с`);

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
                                end: slot.time,
                                price: slot.price
                            };
                        } else if (new Date(`1970-01-01T${currentSlot.end}:00Z`) >= new Date(`1970-01-01T${slot.time}:00Z`)) {
                            // currentSlot.end = getNextTime(slot.time);
                            currentSlot.price += slot.price;
                        } else {
                            combinedSlots.push(currentSlot);
                            currentSlot = {
                                start: slot.time,
                                court_id: slot.court_id,
                                end: slot.time,
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

                        $('.slot.next_slot').each(function () {
                            if ($(this).data('field') === field && ($(this).data('time') >= start || $(this).data('time') < end)) {
                                $(this).removeClass('next_slot');
                                previousSelectedSlot = null;
                            }
                        });

                        $('.total-price').text(`0 т.с`);

                        updateSelectedSlots();
                    });
                }
            }

            @if($isUpdate)
                setTimeout(() => updateSelectedSlots(true), 1000);
            @endif

            let previousSelectedSlot = null;

            $(document).on('click', '.slot', function () {
                const $this = $(this);
                const isSelected = $this.hasClass('selected');
                const isBooked = $this.hasClass('slot_booked');
                const isFirstSlot = $this.siblings('.selected').length === 0;
                const isNextSlot = $this.hasClass('next_slot');

                if (!isBooked) {
                    // Toggle 'selected' class
                    $this.toggleClass('selected');

                    // Determine the next time slot
                    const nextTime = getNextTime($this.data('time'));
                    const $nextSlot = $this.siblings(`.slot[data-time="${nextTime}"]`);

                    if ($nextSlot.length && !($nextSlot.hasClass('slot_booked') || $nextSlot.hasClass('selected'))) {
                        $nextSlot.toggleClass('next_slot');
                    }

                    // Handle the logic for selecting and deselecting slots
                    if (!isSelected) {
                        if (previousSelectedSlot && previousSelectedSlot.data('field') === $this.data('field')) {
                            autoSelectSlotsInRange(previousSelectedSlot, $this);
                        } else {
                            previousSelectedSlot = $this;
                        }
                    } else {
                        previousSelectedSlot = null;
                    }

                    updateSelectedSlots();
                }

                // Update book button state
                const selectedSlots = [];
                $('.selected-slots .selected-slot').each(function () {
                    selectedSlots.push({
                        court_id: $(this).find('h2').data('court-id'),
                        start_time: $(this).find('span[data-start-time]').data('start-time'),
                        end_time: $(this).find('span[data-end-time]').data('end-time'),
                        price: parseInt($(this).find('.cost_cancel_section h2').text().replace('т.с', '').trim(), 10)
                    });
                });

                if (selectedSlots.length >= 1) {
                    $('.book').removeClass('disabled');
                } else {
                    saveSlotsToLocalStorage([]);
                    $('.book').addClass('disabled');
                }
            });


            function saveSlotsToLocalStorage(selectedSlots) {
                localStorage.setItem('selectedSlots', JSON.stringify(selectedSlots));
            }

            function restoreSlotsFromLocalStorage() {
                let savedSlots = JSON.parse(localStorage.getItem('selectedSlots'));

                if (savedSlots && savedSlots.length > 0) {
                    // Объединяем слоты по court_id
                    const groupedSlots = savedSlots.reduce((acc, slot) => {
                        if (!acc[slot.court_id]) {
                            acc[slot.court_id] = [];
                        }
                        acc[slot.court_id].push(slot);
                        return acc;
                    }, {});

                    // Обрабатываем каждый court_id
                    Object.keys(groupedSlots).forEach(court_id => {
                        const slots = groupedSlots[court_id];

                        // Добавляем классы ко всем слотам court_id
                        slots.forEach((slot, index) => {
                            const slotElement = $(`.slot[data-court-id="${slot.court_id}"][data-time="${slot.time}"]`);
                            slotElement.addClass('selected').addClass('next_slot');
                            
                            // Если это первый слот, убираем класс 'next_slot'
                            if (index === 0) {
                                slotElement.removeClass('next_slot');
                            }
                        });

                        // Убираем класс 'selected' у последнего слота
                        const lastSlot = slots.at(-1);
                        if (lastSlot) {
                            const lastSlotElement = $(`.slot[data-court-id="${lastSlot.court_id}"][data-time="${lastSlot.time}"]`);
                            lastSlotElement.removeClass('selected');
                        }
                    });

                    // Обновляем выбранные слоты
                    updateSelectedSlots(true);
                }
            }




            $(document).ready(function () {
                restoreSlotsFromLocalStorage();
            });

            function clearSlotsFromLocalStorage() {
                localStorage.removeItem('selectedSlots');
            }


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

                if (selectedSlots.length > 1) {
                    console.log("Пожалуйста, выберите один из доступных кортов.")
                    let errorHtml = `<div class="alert alert-solid-danger" role="alert"><li>Пожалуйста, выберите один из доступных кортов.</li></div>`;
                    $('.res_error').empty();
                    $('.res_error').append(errorHtml);
                    $('#error_modal').fadeIn().delay(5000).fadeOut();
                } else {
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
                                    bot_user_id: user.id,
                                    full_name: `${user.first_name} ${user.second_name || ''}`.trim(),
                                    phone_number: user.phone,
                                    slots: selectedSlots,
                                    date: selectedDate,
                                    source: 'bot'
                                };

                                @if($isUpdate)
                                    bookingData.date = @json($userBook->date);
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
                                        clearSlotsFromLocalStorage();
                                        @if (!$isUpdate)
                                        initiatePaycomPayment(response.booking_ids, response.total_sum);
                                        @else
                                            window.location.href = '{{ route('findz.mybookings', ['sportType' => $currentSportTypeId]) }}';
                                        @endif
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
                                let errorHtml = `<div class="alert alert-solid-danger" role="alert"><li>Ошибка: пользователь не найден</li></div>`;
                                $('.res_error').empty();
                                $('.res_error').append(errorHtml);
                                $('#error_modal').fadeIn().delay(5000).fadeOut();
                            }
                        },
                        error: function (err) {
                            console.log('Ошибка при получении данных пользователя', err);
                            let errors = err.responseJSON.message;
                            let errorHtml = `<div class="alert alert-solid-danger" role="alert"><li>Ошибка при получении данных пользователя</li></div>`;
                            $('.res_error').empty();
                            $('.res_error').append(errorHtml);
                            $('#error_modal').fadeIn().delay(5000).fadeOut();
                        }
                    });

                    function initiatePaycomPayment(bookingId, amount) {
                        let formattedAmount = parseFloat(amount).toFixed(2);

                        let paycomForm = `
                        <form id="form-payme" method="POST" action="https://checkout.paycom.uz">
                            <input type="hidden" name="merchant" value="66cdfb052f8d5ff4746f8435">
                            <input type="hidden" name="account[book_id]" value="${bookingId[0]}">
                            <input type="hidden" name="amount" value="${formattedAmount}">
                            <input type="hidden" name="lang" value="{{app()->getLocale()}}">
                            <input type="hidden" name="callback" value="{{ route('findz.mybookings', ['sportType' => $currentSportTypeId]) }}">
                            <input type="hidden" name="button" data-type="svg" value="colored">
                        </form>
                    `;

                        $('body').append(paycomForm);
                        $('#form-payme').submit();
                    }

                    $('#error_modal img').click(function () {
                        $('.error_modal').hide();
                    });
                }
            });
        });

    </script>
@endsection

