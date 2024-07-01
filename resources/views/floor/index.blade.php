@extends('layouts.app')

@section('content')
    <div class="card">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-header">Этажи</h5>

            <div class="d-flex justify-content-center align-items-center">

                <div class="card-body">
                    <div class="dropdown bootstrap-select" style="width: 200px; height: 40px; margin-right: 10px">
                        <select id="selectpickerBasic" name="apartment_count" class="selectpicker w-100"
                                data-style="btn-default" tabindex="null">
                            @foreach($houses as $house)
                                <option
                                    value="{{ $house->id }}" @selected($house->id == (isset(request()->segments()[1]) ? request()->segments()[1] : 1) )>{{ $house->name }}</option>
                            @endforeach
                            <option value="all">All</option>
                            <option disabled @selected(!isset(request()->segments()[1]))>По дому</option>
                        </select>
                    </div>
                </div>

                <a href="{{ route('floors.create') }}" class="btn btn-primary" style="margin-right: 22px;">Создать</a>
            </div>
        </div>


        <div class="card-datatable table-responsive">
            <table class="datatables-users table border-top">
                <thead>
                <tr>
                    <th>Id</th>
                    <th>Номер</th>
                    <th>Количество квартир</th>
                    <th>Дом</th>
                    <th></th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                @foreach($floors as $floor)
                    <tr>
                        <td>{{ $floor->id }}</td>
                        <td>{{ $floor->number }}</td>
                        <td>{{ $floor->apartment_count }}</td>
                        <td>{{ $floor->house->name }}</td>
                        <td>
                            <a href="{{ route('floors.edit', $floor->id) }}" class="btn btn-warning"
                               style="margin-right: 22px;">Редактировать</a>
                        </td>
                        <td>
                            <form action="{{ route('floors.destroy', $floor->id) }}" method="POST"
                                  style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger" style="margin-left: -40px !important;">Удалить</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.getElementById('selectpickerBasic').addEventListener('change', function () {
            var houseId = this.value;
            if (houseId && houseId === 'all') {
                window.location.href = `{{ route('floors.index') }}`
            } else {
                window.location.href = '/floors-by-house/' + houseId;
            }
        });
    </script>
@endsection
