@extends('layouts.app')

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"> Создать расписание для корта</h5>
        </div>
        @if ($errors->any())
            <div class="alert alert-solid-danger" role="alert">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </div>
        @endif
        <div class="card-body">
            <form action="{{ route('schedule.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="court" class="form-label">Корт</label>
                    <select name="court_id" id="court" class="form-control">
                        <option value="">Все корты</option>
                        @foreach($courts as $court)
                            <option
                                value="{{ $court->id }}" {{ request('stadium_id') == $court->id ? 'selected' : '' }}>
                                {{ $court->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label for="date" class="form-label">Дата</label>
                    <input type="date" name="date" id="date" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Часы</label>
                    <div id="hours-container">
                        <div class="hour-row">
                            <div class="row">
                                <div class="col-md-5">
                                    <input type="time" name="hours[0][start_time]" class="form-control" required>
                                </div>
                                <div class="col-md-5">
                                    <input type="time" name="hours[0][end_time]" class="form-control" required>
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-danger remove-hour">Удалить</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <button type="button" id="add-hour" class="btn btn-secondary mt-2">Добавить час</button>
                </div>
                <button type="submit" class="btn btn-primary">Сохранить</button>
            </form>
        </div>
    </div>
@endsection
@section('scripts')
    <script>
        $(document).ready(function () {
            let hourIndex = 1;

            $('#add-hour').on('click', function () {
                const newHourRow = `
            <div class="hour-row">
                <div class="row mt-2">
                    <div class="col-md-5">
                        <input type="time" name="hours[${hourIndex}][start_time]" class="form-control" required>
                    </div>
                    <div class="col-md-5">
                        <input type="time" name="hours[${hourIndex}][end_time]" class="form-control" required>
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-danger remove-hour">Удалить</button>
                    </div>
                </div>
            </div>
        `;
                $('#hours-container').append(newHourRow);
                hourIndex++;

                // Добавляем событие для кнопки удаления
                $('.remove-hour').last().on('click', function () {
                    $(this).closest('.hour-row').remove();
                });
            });

            // Добавляем событие для кнопки удаления существующих строк
            $('.remove-hour').on('click', function () {
                $(this).closest('.hour-row').remove();
            });
        });

    </script>
@endsection
