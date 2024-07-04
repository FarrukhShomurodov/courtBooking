@extends('layouts.app')

@section('content')
    <div class="card">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-header">Корты</h5>
            <a href="{{ route('schedule.create') }}" class="btn btn-primary" style="margin-right: 22px;">Создать</a>
        </div>
        @if ($errors->any())
            <div class="alert alert-solid-danger" role="alert">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </div>
        @endif
        <div class="card-body">
            <div class="mb-3">
                <label for="courtDropdown" class="form-label">Стадион</label>
                <div class="dropdown">
                    <button class="btn btn-default dropdown-toggle w-100 d-flex justify-content-between"
                            type="button" id="courtDropdown" data-bs-toggle="dropdown" aria-expanded="false"
                            style="border: 1px solid #d4d8dd; padding: .535rem 1.375rem .535rem .75rem;">
                        Выбрать корт
                    </button>
                    <ul class="dropdown-menu w-100" aria-labelledby="courtDropdown">
                        @foreach($courts as $court)
                            <li><a class="dropdown-item" href="#"
                                   data-value="{{ $court->id }}">{{ $court->name }}</a></li>
                        @endforeach
                            <li><a class="dropdown-item" href="#"
                                   data-value="all">Все</a></li>
                    </ul>
                    <input type="hidden" name="stadium_id" id="courtInput">
                </div>
            </div>
            <button class="btn btn-primary filtr">Филтр</button>
        </div>
    </div>

    <div class="card mt-4">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-header">Расписание кортов</h5>
        </div>
        <div class="card-body">
            <div class="mb-4 schedule">

            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function () {
            const dropdowns = [
                {dropdown: $('#courtDropdown'), input: $('#courtInput')}
            ];
            const originalBorderColor = '#d4d8dd';

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

            dropdowns.forEach(({dropdown, input}) => {
                dropdown.next('.dropdown-menu').on('click', '.dropdown-item', function (e) {
                    e.preventDefault();
                    updateDropdownSelection(dropdown, input, $(this).data('value'), $(this).text(), dropdown.next('.dropdown-menu'));
                });

                dropdown.on('focus', () => {
                    dropdown.css('borderColor', '#5a8dee');
                });
                dropdown.on('blur', () => {
                    dropdown.css('borderColor', originalBorderColor);
                });
            });

            function allCourts() {
                return `
                     @foreach($courts as $court)
                         @if($court->days->count() >= 1)
                            <h5>{{ $court->name }}</h5>
                            <div class="mb-3">
                                <ul class="list-group">
                                @foreach($court->days as $day)
                                        <li class="list-group-item">
                                        <strong>{{ $day->date }}</strong>
                                            @foreach($day->hours as $hour)
                                            <ul>
                                                <li>{{ $hour->start_time }} - {{ $hour->end_time }} <span class="text-danger"><b>{{ $hour->is_booked ? 'booked' : ''}}</b></span></li>
                                            </ul>
                                            @endforeach
                                        <button class="btn btn-warning mt-2" onclick="location.href='{{route('schedule.edit', $day->id )}}'">Редактировать</button>
                                        <form action="{{ route('schedule.destroy', $day->id) }}" method="POST"
                                          style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-danger mt-2">Удалить</button>
                                        </form>
                                        </li>
                                @endforeach
                                </ul>
                            </div>
                         @endif
                    @endforeach
                        `
            }

            $('.schedule').append(allCourts());


            $('.filtr').click(function () {
                let courtId = $('#courtInput').val();
                $('.schedule').empty();

                if(courtId && courtId === 'all'){
                    $('.schedule').append(allCourts);
                }else{
                    $.ajax({
                        url: `/api/courts/${courtId}`,
                        method: 'get',
                        success: function (res) {
                            console.log(res)
                            let schedules = `<h5>${res.court.name}</h5>`;

                            let days = res.days;

                            for (let i = 0; i < days.length; i++) {

                                let hours = days[i].hours;
                                let hoursHtml = '';

                                for (let i = 0; i < hours.length; i++) {
                                    hoursHtml += ` <li>${hours[i].start_time} - ${hours[i].end_time}  <span class="text-danger"><b>${hours[i].is_booked ? 'booked' : ''}</b></span></li>`
                                }

                                schedules += `
                                <div class="mb-3">
                                    <ul class="list-group">
                                        <li class="list-group-item">
                                            <strong>${days[i].date}</strong>
                                                <ul>
                                                    ${hoursHtml}
                                                </ul>
                                                <button class="btn btn-warning mt-2" onclick="location.href='schedule/edit/${days[i].id}'">Редактировать</button>
                                                <form action="schedule/destroy/${days[i].id}" method="POST" style="display:inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn btn-danger mt-2">Удалить</button>
                                                </form>
                            </li>
                        </ul>
                    </div>
`
                            }

                            $('.schedule').append(schedules);
                        }.bind(this),
                        error: function (error) {
                            console.error('Error deleting photo:', error);
                        }
                    });
                }
            })
        })
    </script>
@endsection
