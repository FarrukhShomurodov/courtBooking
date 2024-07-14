@php
    use Carbon\Carbon;
@endphp
@extends('layouts.app')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        @if ($errors->any())
            <div class="alert alert-solid-danger" role="alert">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </div>
        @endif
        <div class="card app-calendar-wrapper">
            <div class="row g-0">
                <div class="col app-calendar-sidebar" id="app-calendar-sidebar">
                    <div class="border-bottom p-4 my-sm-0 mb-3">
                        <div class="d-grid">
                            <button class="btn btn-primary btn-toggle-sidebar" data-bs-toggle="offcanvas"
                                    data-bs-target="#addEventSidebar" aria-controls="addEventSidebar">
                                <i class="bx bx-plus me-1"></i>
                                <span class="align-middle">Добавить бронь</span>
                            </button>
                        </div>
                    </div>
                    <div class="p-4">
                        <div class="ms-n2">
                            <div class="inline-calendar"></div>
                        </div>
                    </div>
                </div>
                <div class="col app-calendar-content">
                    <div class="card" style="box-shadow: 0 0 0 !important">
                        <div class="card-body" style="padding: 0 !important;">
                            <div class="table-responsive text-nowrap">
                                <table class="table table-bordered">
                                    <thead>
                                    <tr>
                                        <th>Время</th>
                                        @foreach($courts as $court)
                                            <th data-court-id="{{ $court->id }}">{{ $court->name }}</th>
                                        @endforeach
                                    </tr>
                                    </thead>

                                    <tbody class="table-border-bottom-0">
                                    @for ($i = 0; $i < 24; $i++)
                                        <tr>
                                            <td>{{ sprintf('%02d:00', $i) }}</td>
                                            @foreach($courts as $court)
                                                @php
                                                    $bookingId = 0;
                                                    $hasBooking = false;
                                                    $currentTime = Carbon::createFromTime($i, 0, 0);
                                                    foreach($court->bookings as $booking) {
                                                        $bookingId = $booking->id;
                                                        if (Carbon::parse($booking->date)->isToday()) {
                                                            $bookingStartTime = Carbon::parse($booking->start_time);
                                                            $bookingEndTime = Carbon::parse($booking->end_time);
                                                            if ($currentTime->between($bookingStartTime, $bookingEndTime)) {
                                                                $hasBooking = true;
                                                                break;
                                                            }
                                                        }
                                                    }
                                                @endphp
                                                <td style="padding: 0px !important;" data-court-id="{{$court->id}}">
                                                    <div class="booking-cell" data-booking-id="{{$bookingId}}"
                                                         style="width: 100%; height: 43.5px; @if($hasBooking) background-color: #ff294d; @endif"></div>
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endfor
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="app-overlay"></div>
                    <div class="offcanvas offcanvas-end event-sidebar" tabindex="-1" id="addEventSidebar"
                         aria-labelledby="addEventSidebarLabel">
                        <div class="offcanvas-header border-bottom d-flex align-items-center align-content-center">
                            <h5 id="modalDateTitle">Добавить бронь</h5>
                            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"
                                    aria-label="Close"></button>
                        </div>
                        <div class="offcanvas-body">
                            <form action="{{ route('bookings.store') }}" method="POST">
                                @csrf
                                <div class="res_error"></div>

                                <div class="mb-3 fv-plugins-icon-container fv-plugins-bootstrap5-row-valid">
                                    <label class="form-label" for="eventStartDate">Дата</label>
                                    <input type="date" name="date" class="form-control flatpickr-input"
                                           id="eventStartDate" placeholder="Дата" readonly="readonly">
                                    <div
                                        class="fv-plugins-message-container fv-
                                        -message-container--enabled invalid-feedback">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="courtDropdown" class="form-label">Корт</label>
                                    <div class="dropdown">
                                        <button
                                            class="btn btn-default dropdown-toggle w-100 d-flex justify-content-between"
                                            type="button" id="courtDropdown" data-bs-toggle="dropdown"
                                            aria-expanded="false"
                                            style="border: 1px solid #d4d8dd; padding: .535rem 1.375rem .535rem .75rem;">
                                            Выберите корт
                                        </button>
                                        <ul class="dropdown-menu w-100" aria-labelledby="courtDropdown">
                                            @foreach($courts as $court)
                                                <li><a class="dropdown-item" href="#"
                                                       data-value="{{ $court->id }}">{{ $court->name }}</a></li>
                                            @endforeach
                                        </ul>
                                        <input type="hidden" name="court_id" id="courtInput">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="fullName">ФИО</label>
                                    <input type="text" name="full_name" class="form-control" id="fullName"
                                           placeholder="Фамилия Имя Очество" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="phoneNumber">Номер телефона</label>
                                    <input type="text" name="phone_number" class="form-control" id="phoneNumber"
                                           placeholder="Номер телефона" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Время</label>
                                    <div id="hours-container">
                                        <div class="hour-row">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <label for="from">От: </label>
                                                    <select id="from"
                                                            class="form-select small-select"
                                                            data-live-search="true">
                                                    </select>
                                                </div>
                                                <div class="col-md-4">
                                                    <label for="to">До: </label>
                                                    <select id="to"
                                                            class="form-select small-select"
                                                            data-live-search="true">
                                                    </select>
                                                </div>
                                                <div class="col-md-4">
                                                    <label for="cost">Цена</label>
                                                    <input id="cost" class="cost form-control" value="0"
                                                           style="height: 29.59px" readonly>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{--hours --}}
                                <input type="hidden" name="start_time">
                                <input type="hidden" name="end_time">

                                <button type="submit" class="btn btn-primary">Готово</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="bookingModal" tabindex="-1" aria-labelledby="bookingModalLabel"
                 aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="bookingModalLabel">Booking Details</h5>
                        </div>
                        <div class="modal-body">
                            <p><strong>ID:</strong> <span id="bookingId"></span></p>
                            <p><strong>ФИО:</strong> <span id="bookingFullName"></span></p>
                            <p><strong>Номер телефона:</strong> <span id="bookingPhoneNumber"></span></p>
                            <p><strong>Дата:</strong> <span id="bookingDateInfo"></span></p>
                            <p><strong>Время начала:</strong> <span id="bookingStartTime"></span></p>
                            <p><strong>Время конца:</strong> <span id="bookingEndTime"></span></p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function () {
            const courtDropdown = $('#courtDropdown');
            const courtInput = $('#courtInput');

            function updateDropdownSelection(dropdown, input, value, text, dropdownMenu) {
                const prevSelected = dropdownMenu.find('.dropdown-item.selected');
                if (prevSelected.length > 0) {
                    prevSelected.removeClass('selected').css({
                        backgroundColor: '',
                        color: ''
                    });
                }

                dropdown.text(text);

                input.val(value);
                dropdown.css('borderColor', '#5a8dee');

                const selectedItem = dropdownMenu.find(`[data-value="${value}"]`);

                if (selectedItem.length > 0) {
                    selectedItem.addClass('selected').css({
                        backgroundColor: 'rgba(90, 141, 238, .08)',
                        color: '#5a8dee'
                    });
                }

                setTimeout(() => {
                    dropdown.css('borderColor', '#d4d8dd');
                }, 1000);
            }

            $('#courtDropdown + .dropdown-menu').on('click', '.dropdown-item', function (e) {
                e.preventDefault();
                updateDropdownSelection(courtDropdown, courtInput, $(this).data('value'), $(this).text(), courtDropdown.next('.dropdown-menu'));

                const courtId = courtInput.val();
                if (courtId) {
                    fetchSchedule(courtId);
                }
            });

            function fetchSchedule(courtId, defaultCourtDropdown) {
                $('#from').html('');
                $('#to').html('');
                $('.cost').val(0);

                $.ajax({
                    url: `/api/courts/${courtId}`,
                    method: 'GET',
                    success: function (res) {
                        console.log(res)

                        let fromOptions = '';
                        let toOptions = '';

                        for (let i = 0; i < res.length; i++) {
                            let startTime = res[i].start_time.substring(0, 5);
                            let endTime = res[i].end_time.substring(0, 5);

                            fromOptions += `<option value="${startTime}">${startTime}</option>`;
                            toOptions += `<option value="${endTime}">${endTime}</option>`;
                        }

                        $('#from').append(fromOptions);
                        $('#to').append(toOptions);
                        $('.selectpicker').selectpicker('refresh');

                        updatePrices()
                    },
                    error: function (error) {
                        let errors = error.responseJSON.error;
                        let errorHtml = `<div class="alert alert-solid-danger" role="alert"><li>${errors}</li></div>`;
                        $('.res_error').append(errorHtml);

                        setTimeout(function () {
                            $('.res_error').html('');
                        }, 3000);

                        const firstOption = courtDropdown.next('.dropdown-menu').find('.dropdown-item').first();
                        if (firstOption.length > 0) {
                            updateDropdownSelection(courtDropdown, courtInput, firstOption.data('value'), firstOption.text(), courtDropdown.next('.dropdown-menu'));
                        }
                    }
                });
            }

            $('.flatpickr-input').change( function () {
                fetchBookingsForDate($(this).val());
            })

            $('input[name=date]').flatpickr({
                minDate: 'today',
                enableTime: false,
            })

            function fetchBookingsForDate(dateStr) {
                $.ajax({
                    url: '/api/booking-by-date',
                    method: 'POST',
                    data: {
                        date: dateStr,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (response) {
                        displayBookings(response);
                    },
                    error: function (err) {
                        console.log(err);
                    }
                });
            }


            function displayBookings(bookings) {
                $('.table tbody tr td').css('background-color', 'white');
                $('.table tbody tr div').css('background-color', '').removeClass('booking-cell');

                bookings.forEach(booking => {
                    const courtId = booking.court_id;
                    const startTime = booking.start_time;
                    const endTime = booking.end_time;
                    const bookingId = booking.id;

                    const startHour = parseInt(startTime.split(':')[0], 10);
                    const endHour = parseInt(endTime.split(':')[0], 10);

                    const courtColumn = $(`th[data-court-id="${courtId}"]`).index();

                    if (courtColumn !== -1) {
                        for (let i = startHour; i <= endHour; i++) {
                            const cell = $(`.table tbody tr:eq(${i}) td:eq(${courtColumn})`);
                            cell.css('background-color', '#ff294d');
                            cell.find('div').addClass('booking-cell').data('booking-id', bookingId);
                        }
                    }
                });
            }

            function formatTimeToHIS(time) {
                const [hours, minutes] = time.split(':');
                const normalizedHours = hours.padStart(2, '0');
                const normalizedMinutes = minutes.padStart(2, '0');
                return `${normalizedHours}:${normalizedMinutes}:00`;
            }

            function updatePrices() {
                $('.res_error').html('');
                $('#hours-container .hour-row').each(function () {
                    const row = $(this);
                    const startTime = formatTimeToHIS(row.find('#from').val());
                    const endTime = formatTimeToHIS(row.find('#to').val());
                    const courtId = $('#courtInput').val();

                    if (!courtId) {
                        let errorHtml = `<div class="alert alert-solid-danger" role="alert"><li>Select a court</li></div>`;
                        $('.res_error').append(errorHtml);

                        setTimeout(function () {
                            $('.res_error').html('');
                        }, 5000);
                    }

                    if (startTime && endTime && courtId) {
                        $('[name=start_time]').val(startTime);
                        $('[name=end_time]').val(endTime);

                        $.ajax({
                            url: '/api/price-by-time',
                            method: 'POST',
                            data: {
                                court_id: courtId,
                                start_time: startTime,
                                end_time: endTime,
                                _token: $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function (response) {
                                row.find('.cost').val(response.total_cost || 0);
                            },
                            error: function (err) {
                                let errors = err.responseJSON.message;
                                let errorHtml = `<div class="alert alert-solid-danger" role="alert"><li>${errors}</li></div>`;
                                $('.res_error').append(errorHtml);

                                setTimeout(function () {
                                    $('.res_error').html('');
                                }, 5000);
                            }
                        });
                    }
                });
            }

            $('#hours-container').on('change', '#from, #to', function () {
                updatePrices();
            });

            $(document).on('click', '.booking-cell', function () {
                const bookingId = $(this).data('booking-id');

                $.ajax({
                    url: `/api/booking/${bookingId}`,
                    method: 'GET',
                    success: function (response) {
                        $('#bookingId').text(response.id);
                        $('#bookingFullName').text(response.full_name);
                        $('#bookingPhoneNumber').text(response.phone_number);
                        $('#bookingDateInfo').text(response.date);
                        $('#bookingStartTime').text(response.start_time);
                        $('#bookingEndTime').text(response.end_time);

                        $('#bookingModal').modal('show');
                    },
                    error: function (err) {
                        console.log(err);
                    }
                });

            });
        });
    </script>
@endsection
