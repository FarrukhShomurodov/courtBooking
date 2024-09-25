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
    <style>
        #courtDescription{
            overflow-wrap: break-word;
        }
    </style>
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
                        @php $isAllowedClick = is_null($court->photos) && is_null($court->description); @endphp
                        <th @class([
                           'court_name',
                           'pointer' => !$isAllowedClick,
                           'with-triangle' => !$isAllowedClick
                          ]) data-court-id="{{ $court->id }}">{{ $court->name }}</th>
                    @endforeach
                </tr>
                </thead>

                <tbody class="table-border-bottom-0">
                <tr></tr>
                </tbody>
            </table>
        </div>

        <div class="selected-slots">
        </div>

        <div class="total">
            <h1>{{ __('findz/book.Итого:') }}</h1>
            <h1 class="total-price">0 {{ __('findz/book.т.с') }}</h1>
        </div>

        <div class="user-info mt-30">
            <h3>{{ __('findz/book.user_info') }}</h3>
            <div class="form-group">
                <label for="user_name">{{ __('findz/book.name') }}</label><br>
                <input class="w-100 user-info-form" type="text" id="user_name" name="user_name"
                       placeholder="{{ __('findz/book.name') }}" required>
            </div>
            <div class="form-group">
                <label for="user_phone">{{ __('findz/book.phone_number') }}</label><br>
                <input class="w-100 user-info-form" type="tel" id="user_phone" name="user_phone"
                       placeholder="+998XX12345678" required>
            </div>
        </div>

        {{--        @if(!$isUpdate)--}}
        {{--            <div class="payment-method mt-30">--}}
        {{--                <h3>{{ __('findz/book.Способ Оплаты') }}</h3>--}}
        {{--                <div class="payment-options">--}}
        {{--                    <button class="active-payment" data-payment="payme">{{ __('PayMe') }}</button>--}}
        {{--                    <button data-payment="uzum">{{ __('Uzum') }}</button>--}}
        {{--                </div>--}}
        {{--            </div>--}}
        {{--        @endif--}}

        <div class="booking-rules mt-30">
            <p>{{ __('findz/book.Правила брони') }}</p>
            <ul>
                <li>{{ __('findz/book.Рекомендуется быть на месте за 15 минут до начала') }}</li>
                <li>{{ __('findz/book.can_not_edit_book_more_one') }}</li>
            </ul>
        </div>

        <div class="error_modal" id="error_modal">
            <div class="d-flex justify-content-between align-items-center">
                <span class="res_error"></span>
                <img src="{{ asset('img/findz/icons/close.svg') }}" alt="close btn"/>
            </div>
        </div>

        <div id="courtModal" class="modal">
            <div class="modal-content">
                <div class="d-flex align-items-center justify-content-between">
                    <h2 id="courtName"></h2>
                    <span class="close"><img src="{{ asset('img/findz/icons/close.svg') }}" alt="close btn"/></span>
                </div>
                <span id="courtDescription"></span>
                <div id="courtPhotos" class="court-photos"></div>
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
            // date and show court
            const modal = $('#courtModal');
            const closeBtn = $('.close');

            function truncateText(maxChars) {
                let element = $('#courtDescription');

                if (element.length === 0) {
                    console.error("Элемент с ID 'description' не найден.");
                    return;
                }

                let originalText = element.text().trim();

                if (originalText.length > maxChars) {
                    let truncatedText = originalText.slice(0, maxChars) + '...';
                    element.data('original-text', originalText);
                    element.data('truncated-text', truncatedText);
                    element.text(truncatedText);
                } else {
                    console.log("Текст не превышает максимальную длину. Урезка не требуется.");
                }
            }

            $('#read-more').click(function () {
                let description = $('#courtDescription');
                if (description.hasClass('expanded')) {
                    description.text(description.data('truncated-text'));
                    $(this).text(`{{ __('findz/book.read_more')}}`);
                } else {
                    description.text(description.data('original-text'));
                    $(this).text('Свернуть');
                }
                description.toggleClass('expanded');
            });



            $('.court_name').on('click', function () {
                if ($(this).css('cursor') === 'not-allowed') return;

                const courtId = $(this).data('court-id');

                $.ajax({
                    url: `/api/court-show/${courtId}`,
                    method: 'GET',
                    success: function (response) {
                        $('#courtName').text(response.name);
                        $('#courtDescription').text(response.description);
                        $('#read-more').text(`{{ __('findz/book.read_more')}}`);
                        $('#courtPhotos').empty();

                        if (response.photos) {
                            const photos = JSON.parse(response.photos);
                            const images = photos.map(photo => `<div><img class="stadium_image" src="/storage/${photo}" alt="court photo"/></div>`).join('');
                            const imgCont = `<div class="court_images"><div class="scroll-container">${images}</div></div>`;

                            $('#courtPhotos').append(imgCont);
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
                        }
                        truncateText(100);
                        modal.show();
                    },
                    error: function (error) {
                        alert('Ошибка при загрузке данных корта');
                    }
                });
            });

            closeBtn.on('click', function () {
                modal.hide();
            });

            $(window).on('click', function (event) {
                if ($(event.target).is(modal)) {
                    modal.hide();
                }
            });

            let selectedDate = @json(request()->input('date') ?? date('Y-m-d'));
            @if($isUpdate)
                selectedDate = @json($userBook->date);
            @endif
            let dateObject = new Date(selectedDate);
            let dayOfWeek = getRussianDayOfWeek(dateObject);

            let currentMonth = dateObject.getMonth();
            let currentYear = dateObject.getFullYear();

            let today = new Date();
            let maxDate = new Date();
            maxDate.setDate(today.getDate() + 30);

            flatpickr.localize(flatpickr.l10ns.ru);
            let flatpickrCalendar = flatpickr("#calendar_date", {
                static: true,
                disableMobile: true,
                minDate: "today",
                maxDate: maxDate,
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

                    dateObject = new Date(dateStr);
                    selectedDate = new Date(dateStr);

                    updateButtonState()

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

                if (dateObject >= maxDate) {
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
                        stadium: {{ $stadium->id }},
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

            function formatCost(cost) {
                if (cost >= 1000000) {
                    return (cost / 1000000).toLocaleString('ru-RU', {minimumFractionDigits: 1}) + ' M';
                } else {
                    return (cost / 1000).toLocaleString('ru-RU') + ' ' + `{{ __('findz/book.currency') }}`;
                }
            }

            // slots
            function updateSlots(data) {
                const savedSlots = JSON.parse(localStorage.getItem('selectedSlots')) || [];

                $('.slots-table tbody tr').empty();
                data.courts.forEach(court => {
                    let row = `<td style="padding: 0px !important;" data-court-id="${court.id}">`;

                    let timeSlots = [];
                    for (let h = 0; h < 24; h++) {
                        let time = ('0' + h).slice(-2) + ':00';
                        timeSlots.push(time);
                    }

                    timeSlots.forEach(timeSlot => {
                        let schedule = court.schedules.find(s => s.start_time.slice(0, 5) === timeSlot);

                        let hasBooking = false;

                        @if($isUpdate)
                        let oldSelectedSlot = (court.id === {{ $userBook->court_id }} && schedule?.start_time == "{{ $userBook->start_time }}" && schedule?.end_time == "{{ $userBook->end_time }}");
                        hasBooking = false;
                        let endTime = "{{ $userBook->end_time }}";
                        endTime = endTime.slice(0, 5);
                        @else
                            hasBooking = data.bookings.some(booking => {
                            let bookingDate = new Date(booking.date).toISOString().slice(0, 10);
                            return court.id == booking.court_id && bookingDate === selectedDate && schedule?.start_time >= booking.start_time && schedule?.end_time <= booking.end_time;
                        });
                        @endif

                        let selected = false;

                        @if($isUpdate)
                            selected = oldSelectedSlot;
                        @else
                            selected = (court.id == {{ request('stadium') }} && (schedule?.start_time.slice(0, 5) >= "{{ $selectedStartTime }}" && schedule?.end_time.slice(0, 5) <= "{{ $selectedEndTime }}"));
                        let endTime = "{{ $selectedEndTime }}";
                        @endif

                        savedSlots.forEach(slot => {
                            if (slot.court_id === court.id && slot.start === timeSlot && slot.date === selectedDate) {
                                selected = true;
                            }
                        });

                        let selectedClass = selected ? 'selected' : '';
                        let slotClass = hasBooking ? 'slot_booked' : '';


                        if (schedule) {
                            row += `
                            <div class="slot ${slotClass} ${selectedClass}"
                                data-start-time="${schedule.start_time.slice(0, 5)}"
                                data-end-time="${schedule.end_time.slice(0, 5)}"
                                data-field="${court.name}"
                                data-price="${Math.round(schedule.cost) / 1000}"
                                data-court-id="${court.id}">
                                ${schedule.start_time.slice(0, 5)}<br>
                                <span>${formatCost(Math.round(schedule.cost))}</span>
                            </div>
                            `;
                        } else {
                            row += `
                            <div class="slot inactive"
                                data-start-time="${timeSlot}"
                                data-court-id="${court.id}">
                                ${timeSlot}<br>
                                <span>{{ __('findz/book.no_slot') }}</span>
                            </div>
                            `;
                        }
                    });

                    row += '</td>';
                    $('.slots-table tbody tr').append(row);
                });
            }

            function updateSelectedSlots() {
                let selectedSlots = [];

                $('.slot.selected').each(function () {
                    const selectedSlot = {
                        field: $(this).data('field'),
                        court_id: $(this).data('court-id'),
                        start: $(this).data('start-time'),
                        end: $(this).data('end-time'),
                        price: parseFloat($(this).data('price')),
                        date: selectedDate
                    };
                    selectedSlots.push(selectedSlot);
                });


                let total = 0;
                $('.selected-slots').empty();
                $('.total-price').text(`${total} т.с`);

                let savedSlots = JSON.parse(localStorage.getItem('selectedSlots')) || [];

                // Добавление новых выбранных слотов
                selectedSlots.forEach(slot => {
                    if (!savedSlots.some(s => s.court_id === slot.court_id && s.start === slot.start && s.end === slot.end && s.date === slot.date)) {
                        savedSlots.push(slot);
                    }
                });

                // Обновление localStorage
                localStorage.setItem('selectedSlots', JSON.stringify(savedSlots));

                if (savedSlots.length > 0) {
                    $('.book').removeClass('disabled');
                } else {
                    $('.book').addClass('disabled');
                }

                savedSlots.forEach(slot => {
                    const dayOfWeek = getRussianDayOfWeek(new Date(slot.date));

                    const slotDiv = $(`
                            <div class="selected-slot">
                                <div>
                                    <h2 data-field="${slot.field}" data-court-id="${slot.court_id}">${slot.field}</h2>
                                    <div>
                                        <span>
                                            <span data-start-time="${slot.start}">${slot.start}</span> -
                                            <span data-end-time="${slot.end}">${slot.end}</span>
                                        </span>
                                        <span class="selected_date">${slot.date}, ${dayOfWeek}</span>
                                    </div>
                                </div>
                                <div class="cost_cancel_section">
                                    <h2>${formatCost(slot.price * 1000)}</h2>
                                    <button class="delete-btn" data-court-id="${slot.court_id}" data-start="${slot.start}" data-end="${slot.end}">
                                        <img src="../../../img/findz/icons/delete_selected_time.svg" alt="delete selected time icon">
                                    </button>
                                </div>
                            </div>
                        `);

                    $('.selected-slots').append(slotDiv);

                    total += slot.price;
                });

                let formatTotal = total * 1000;

                $('.total-price').text(`${formatTotal.toLocaleString('ru-RU')}`);

                // Удаление слотов при нажатии кнопки удаления
                $('.selected-slots').off('click', '.delete-btn').on('click', '.delete-btn', function () {
                    const court_id = $(this).data('court-id');
                    const start = $(this).data('start');
                    const end = $(this).data('end');

                    // Удаляем из savedSlots
                    savedSlots = savedSlots.filter(slot => !(slot.court_id === court_id && slot.start === start && slot.end === end));
                    localStorage.setItem('selectedSlots', JSON.stringify(savedSlots));

                    $(this).closest('.selected-slot').remove();

                    // Снимаем выделение с удаленных слотов
                    $('.slot.selected').each(function () {
                        if ($(this).data('court-id') == court_id && $(this).data('start-time') == start && $(this).data('end-time') == end) {
                            $(this).removeClass('selected');
                        }
                    });

                    updateSelectedSlots();
                });
            }

            if (localStorage.getItem('selectedSlots')) {
                updateSelectedSlots();
            }

            @if($isUpdate)
            setTimeout(() => updateSelectedSlots(), 1000);
            @endif

            $(document).on('click', '.slot', function () {
                const $this = $(this);
                const isBooked = $this.hasClass('slot_booked');

                if (!isBooked) {
                    const price = $this.data('price');

                    @if($isUpdate)
                    if({{ round($userBook->price) / 1000 }} !== price){
                        let errorHtml = `<div class="alert alert-solid-danger" role="alert"><li>Пожалуйста, выберите другое время, так как указанная цена не соответствует вашему выбору.</li></div>`;
                        $('.res_error').empty();
                        $('.res_error').empty();
                        $('.res_error').append(errorHtml);
                        $('#error_modal').fadeIn().delay(5000).fadeOut();
                        return;
                    }
                    @endif

                    $this.toggleClass('selected');

                    const courtId = $this.data('court-id');
                    const startTime = $this.data('start-time');
                    const endTime = $this.data('end-time');

                    let savedSlots = JSON.parse(localStorage.getItem('selectedSlots')) || [];

                    if ($this.hasClass('selected')) {
                        const newSlot = {
                            field: $this.data('field'),
                            court_id: courtId,
                            start: startTime,
                            end: endTime,
                            price: parseFloat($this.data('price')),
                            date: selectedDate
                        };
                        savedSlots.push(newSlot);
                    } else {
                        savedSlots = savedSlots.filter(slot => !(slot.court_id === courtId && slot.start === startTime && slot.end === endTime && slot.date === selectedDate));
                    }

                    localStorage.setItem('selectedSlots', JSON.stringify(savedSlots));
                    updateSelectedSlots();
                }
            });

            // Payment method select
            {{--$('.payment-options button').click(function () {--}}

            {{--    const paymentMethod = $(this).data('payment');--}}

            {{--    $('.payment-options button').removeClass('active-payment');--}}
            {{--    $(this).addClass('active-payment');--}}

            {{--    @if(app() -> getLocale() == 'ru')--}}
            {{--        $('#close-btn').text(`Оплатить через ${paymentMethod.charAt(0).toUpperCase() + paymentMethod.slice(1)}`);--}}
            {{--    @else--}}
            {{--        $('#close-btn').text(`${paymentMethod.charAt(0).toUpperCase() + paymentMethod.slice(1)} orqali to\'lash`);--}}
            {{--    @endif--}}
            {{--});--}}


            // Book
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

                        $('#user_name').val(`${user.first_name} ${user.second_name || ''}`.trim())
                        $('#user_phone').val(user.phone)
                    }
                }
            });

            $(document).on('click', '.book', function () {
                let savedSlots = JSON.parse(localStorage.getItem('selectedSlots')) || [];

                const bookingData = {
                    bot_user_id: 1,
                    full_name: $('#user_name').val(),
                    phone_number: $('#user_phone').val(),
                    slots: savedSlots,
                    source: 'bot'
                };

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

                            @if($isUpdate)
                                if(savedSlots.length > 1){
                                    let errorHtml = `<div class="alert alert-solid-danger" role="alert"><li>Пожалуйста, выберите одно время для бронирования.</li></div>`;
                                    $('.res_error').empty();
                                    $('.res_error').empty();
                                    $('.res_error').append(errorHtml);
                                    $('#error_modal').fadeIn().delay(5000).fadeOut();
                                }else{
                                    $.ajax({
                                        url: '/api/booking/{{$userBook->id}}',
                                        method: 'PUT',
                                        data: bookingData,
                                        success: function (response) {
                                            localStorage.removeItem("selectedSlots")
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
                                }
                            @else
                                $.ajax({
                                    url: '/api/booking',
                                    method: 'POST',
                                    data: bookingData,
                                    success: function (response) {
                                        localStorage.removeItem("selectedSlots")
                                        @if(!$isUpdate)
                                            initiatePaycomPayment(response.booking_id, response.total_sum);
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
                    let formattedAmount = Math.round(amount);
                    let callback = `https://st40.online/telegram/mybookings?sportType={{$currentSportTypeId}}&bot_user_id=${chat_id}`;

                    let paycomForm = `
                        <form id="form-payme" method="POST" action="https://checkout.paycom.uz">
                            <input type="hidden" name="merchant" value="66cdfb052f8d5ff4746f8435">
                            <input type="hidden" name="account[book_id]" value="${bookingId}">
                            <input type="hidden" name="amount" value="${formattedAmount * 100}">
                            <input type="hidden" name="lang" value="{{app()->getLocale()}}">
                            <input type="hidden" name="callback" value="${callback}">
                            <input type="submit" value="">
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

