@extends('layouts.app')

@section('content')
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Создать бронирование</h5>
        </div>
        @if ($errors->any())
            <div class="alert alert-solid-danger" role="alert">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </div>
        @endif
        <div class="card-body">
            <form action="{{ route('bookings.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="courtDropdown" class="form-label">Корт</label>
                    <div class="dropdown">
                        <button class="btn btn-default dropdown-toggle w-100 d-flex justify-content-between"
                                type="button" id="courtDropdown" data-bs-toggle="dropdown" aria-expanded="false"
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
                @if(Auth::user()->roles()->first()->name == 'admin' || Auth::user()->roles()->first()->name == 'owner stadium')
                    <div class="mb-3">
                        <label for="userDropdown" class="form-label">Пользователь</label>
                        <div class="dropdown">
                            <button class="btn btn-default dropdown-toggle w-100 d-flex justify-content-between"
                                    type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false"
                                    style="border: 1px solid #d4d8dd; padding: .535rem 1.375rem .535rem .75rem;">
                                Выберите пользователя
                            </button>
                            <ul class="dropdown-menu w-100" aria-labelledby="userDropdown">
                                @foreach($users as $user)
                                    <li><a class="dropdown-item" href="#"
                                           data-value="{{ $user->id }}">{{ $user->name }}</a></li>
                                @endforeach
                            </ul>
                            <input type="hidden" name="user_id" id="userInput">
                        </div>
                    </div>
                @endif
                <div class="mb-3">
                    <label for="dayDropdown" class="form-label">День</label>
                    <div class="dropdown">
                        <button class="btn btn-default dropdown-toggle w-100 d-flex justify-content-between"
                                type="button" id="dayDropdown" data-bs-toggle="dropdown" aria-expanded="false"
                                style="border: 1px solid #d4d8dd; padding: .535rem 1.375rem .535rem .75rem;">
                            Сначала выберите корт
                        </button>
                        <ul class="dropdown-menu w-100" aria-labelledby="dayDropdown">
                            <li><a class="dropdown-item disabled" href="#">Сначала выберите корт</a></li>
                        </ul>
                        <input type="hidden" name="day_id" id="dayInput">
                    </div>
                </div>
                <div class="mb-3">
                    <label for="hourDropdown" class="form-label">Час</label>
                    <div class="dropdown">
                        <button class="btn btn-default dropdown-toggle w-100 d-flex justify-content-between"
                                type="button" id="hourDropdown" data-bs-toggle="dropdown" aria-expanded="false"
                                style="border: 1px solid #d4d8dd; padding: .535rem 1.375rem .535rem .75rem;">
                            Сначала выберите день
                        </button>
                        <ul class="dropdown-menu w-100" aria-labelledby="hourDropdown">
                            <li><a class="dropdown-item disabled" href="#">Сначала выберите день</a></li>
                        </ul>
                        <input type="hidden" name="hour_id" id="hourInput">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Сохранить</button>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function () {
            const courtDropdown = $('#courtDropdown');
            const courtInput = $('#courtInput');
            const dayDropdown = $('#dayDropdown');
            const dayMenu = dayDropdown.next('.dropdown-menu');
            const dayInput = $('#dayInput');
            const hourDropdown = $('#hourDropdown');
            const hourMenu = hourDropdown.next('.dropdown-menu');
            const hourInput = $('#hourInput');
            const originalBorderColor = '#d4d8dd';
            const userDropdown = $('#userDropdown');
            const userInput = $('#userInput');

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
                    dropdown.css('borderColor', originalBorderColor);
                }, 10);
            }

            $('#courtDropdown + .dropdown-menu').on('click', '.dropdown-item', function (e) {
                e.preventDefault();
                updateDropdownSelection(courtDropdown, courtInput, $(this).data('value'), $(this).text(), courtDropdown.next('.dropdown-menu'));


                dayDropdown.text('Сначала выберите корт');
                dayInput.val('');
                dayMenu.html('<li><a class="dropdown-item disabled" href="#">Сначала выберите корт</a></li>');
                hourDropdown.text('Сначала выберите день');
                hourInput.val('');
                hourMenu.html('<li><a class="dropdown-item disabled" href="#">Сначала выберите день</a></li>');


                const courtId = courtInput.val();
                if (courtId) {
                    fetchDays(courtId);
                }
            });

            dayDropdown.on('focus', () => {
                dayDropdown.css('borderColor', '#5a8dee');
            });

            hourDropdown.on('focus', () => {
                hourDropdown.css('borderColor', '#5a8dee');
            });

            dayDropdown.on('blur', () => {
                dayDropdown.css('borderColor', originalBorderColor);
            });

            hourDropdown.on('blur', () => {
                hourDropdown.css('borderColor', originalBorderColor);
            });

            $('#userDropdown + .dropdown-menu').on('click', '.dropdown-item', function (e) {
                e.preventDefault();
                updateDropdownSelection(userDropdown, userInput, $(this).data('value'), $(this).text(), userDropdown.next('.dropdown-menu'));
            });

            function fetchDays(courtId) {
                $.ajax({
                    url: `/api/days-by-court/${courtId}`,
                    method: 'GET',
                    success: function (days) {
                        dayMenu.empty();
                        if (days.length > 0) {
                            days.forEach(day => {
                                const dayItem = $('<a>', {
                                    class: 'dropdown-item',
                                    href: '#',
                                    text: day.date,
                                    'data-value': day.id
                                });
                                dayMenu.append(dayItem);
                            });

                            dayMenu.on('click', '.dropdown-item', function (e) {
                                e.preventDefault();
                                updateDropdownSelection(dayDropdown, dayInput, $(this).data('value'), $(this).text(), dayDropdown.next('.dropdown-menu'));

                                hourDropdown.text('Сначала выберите день');
                                hourInput.val('');
                                hourMenu.html('<li><a class="dropdown-item disabled" href="#">Сначала выберите день</a></li>');

                                const dayId = dayInput.val();
                                if (dayId) {
                                    fetchHours(dayId);
                                }
                            });
                        } else {
                            const noDaysItem = $('<a>', {
                                class: 'dropdown-item disabled',
                                href: '#',
                                text: 'Дней не найдено'
                            });
                            dayMenu.append(noDaysItem);
                        }
                    },
                    error: function (error) {
                        console.error('Error fetching days:', error);
                        dayMenu.html('<a class="dropdown-item disabled" href="#">Error fetching days</a>');
                    }
                });
            }

            function fetchHours(dayId) {
                $.ajax({
                    url: `/api/hours-by-day/${dayId}`,
                    method: 'GET',
                    success: function (hours) {
                        hourMenu.empty();
                        if (hours.length > 0) {
                            hours.forEach(hour => {
                                const hourItem = $('<a>', {
                                    class: 'dropdown-item',
                                    href: '#',
                                    text: `${hour.start_time} - ${hour.end_time}`,
                                    'data-value': hour.id
                                });
                                hourMenu.append(hourItem);
                            });

                            hourMenu.on('click', '.dropdown-item', function (e) {
                                e.preventDefault();
                                updateDropdownSelection(hourDropdown, hourInput, $(this).data('value'), $(this).text(), hourDropdown.next('.dropdown-menu'));
                            });
                        } else {
                            const noHoursItem = $('<a>', {
                                class: 'dropdown-item disabled',
                                href: '#',
                                text: 'Часов не найдено'
                            });
                            hourMenu.append(noHoursItem);
                        }
                    },
                    error: function (error) {
                        console.error('Error fetching hours:', error);
                        hourMenu.html('<a class="dropdown-item disabled" href="#">Error fetching hours</a>');
                    }
                });
            }
        });
    </script>
@endsection
