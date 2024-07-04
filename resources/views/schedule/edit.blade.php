@extends('layouts.app')

@section('content')
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Редактировать Расписание для даты: {{ $day->date }}</h5>
        </div>
        @if ($errors->any())
            <div class="alert alert-solid-danger" role="alert">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </div>
        @endif
        <div class="card-body">
            <form action="{{ route('schedule.update', $day->id) }}" method="POST" id="schedule-form">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label class="form-label" for="date">Дата</label>
                    <input type="date" name="date" class="form-control" id="date" value="{{ $day->date }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="hours">Часы</label>
                    <div id="hours-container">
                        @foreach($day->hours as $hour)
                            <div class="hour-row">
                                <div class="row mt-2">
                                    <div class="col-md-5">
                                        <input type="time" name="hours[{{ $hour->id }}][start_time]"
                                               class="form-control mb-1" value="{{ $hour->start_time }}" required>
                                    </div>
                                    <div class="col-md-5">
                                        <input type="time" name="hours[{{ $hour->id }}][end_time]"
                                               class="form-control mb-1"
                                               value="{{ $hour->end_time }}" required>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="button" class="btn btn-danger remove-hour">Удалить</button>
                                    </div>
                                    <input type="hidden" name="hours[{{ $hour->id }}][delete]" value="0"
                                           class="delete-input">
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <button type="button" id="add-hour" class="btn btn-secondary">Добавить час</button>
                </div>
                <button type="submit" class="btn btn-primary">Сохранить</button>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function () {
            let hourIndex = {{ $day->hours->count() }};

            $('#add-hour').click(function () {
                let newHourRow = `
                    <div class="hour-row">
                        <div class="row mt-2">
                            <div class="col-md-5">
                                <input type="time" name="hours[new_${hourIndex}][start_time]" class="form-control" required>
                            </div>
                            <div class="col-md-5">
                                <input type="time" name="hours[new_${hourIndex}][end_time]" class="form-control" required>
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-danger remove-hour">Удалить</button>
                            </div>
                            <input type="hidden" name="hours[new_${hourIndex}][delete]" value="0" class="delete-input">
                        </div>
                    </div>
                `;
                $('#hours-container').append(newHourRow);
                hourIndex++;

                // Добавляем событие для кнопки удаления
                $('.remove-hour').off('click').on('click', function () {
                    $(this).closest('.hour-row').find('.delete-input').val(1);
                    $(this).closest('.hour-row').hide();
                });
            });

            // Добавляем событие для кнопки удаления существующих строк
            $('.remove-hour').click(function () {
                $(this).closest('.hour-row').find('.delete-input').val(1);
                $(this).closest('.hour-row').hide();
            });

            $('#schedule-form').submit(function () {
                // Удаляем пустые строки перед отправкой
                $('.hour-row').each(function () {
                    if ($(this).find('.delete-input').val() == 1) {
                        $(this).remove();
                    }
                });
            });
        });
    </script>
@endsection
