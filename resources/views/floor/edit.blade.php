@extends('layouts.app')

@section('content')

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Редактировать этаж</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('floors.update', $floor->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('put')
                <div class="mb-3">
                    <label class="form-label" for="basic-default-fullname">Номер</label>
                    <input type="number" name="number" value="{{ $floor->number }}" class="form-control"
                           id="basic-default-fullname"
                           placeholder="Номер" required>
                </div>
                <div class="mb-3">
                    <label for="selectpickerBasic" class="form-label">Количество квартир</label>
                    <div class="dropdown bootstrap-select w-100">
                        <select type="number" id="selectpickerBasic" name="apartment_count" class="selectpicker w-100"
                                data-style="btn-default" tabindex="null">
                            <option value="1" @selected($floor->apartment_count == 1)>1</option>
                            <option value="2" @selected($floor->apartment_count == 2)>2</option>
                            <option value="3" @selected($floor->apartment_count == 3)>3</option>
                            <option value="4" @selected($floor->apartment_count == 4)>4</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="selectpickerBasic" class="form-label">Дом</label>
                    <div class="dropdown bootstrap-select w-100">
                        <select id="selectpickerBasic" name="house_id" class="selectpicker w-100"
                                data-style="btn-default" tabindex="null">
                            @foreach($houses as $house)
                                <option
                                    value="{{ $house->id }}" @selected( $floor->house_id == $house->id)>{{ $house->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Редактировать</button>
            </form>
        </div>
    </div>

@endsection
