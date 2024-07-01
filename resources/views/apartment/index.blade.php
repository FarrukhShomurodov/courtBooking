@extends('layouts.app')

@section('content')
    <div class="card">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-header">Квартиры</h5>
            <a href="{{ route('apartments.create') }}" class="btn btn-primary" style="margin-right: 22px;">Создать</a>
        </div>
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card-datatable table-responsive">
            <table class="datatables-users table border-top">
                <thead>
                <tr>
                    <th>Id</th>
                    <th>Название</th>
                    <th>Описание</th>
                    <th>Дом</th>
                    <th>Этаж</th>
                    <th></th>
                    <th></th>
                    <th></th>
                </tr>
                </thead>
                <tbody>

                @foreach($apartments as $apartment)
                    <tr>
                        <td>{{ $apartment->id }}</td>
                        <td>{{ $apartment->name }}</td>
                        <td>{{ $apartment->description }}</td>
                        <td>{{ $apartment->house->name }}</td>
                        <td>{{ $apartment->floor->number }}</td>
                        <td>
                            <div class="main__td">
                                @if($apartment->photos_url)
                                    @foreach(json_decode($apartment->photos_url) as $photo_url)
                                        <div class="td__img">
                                            <img src="storage/{{ $photo_url }}" alt="House Photo" class="popup-img"
                                                 width="100px"/>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </td>
                        <td>
                            <a href="{{ route('apartments.edit', $apartment->id) }}" class="btn btn-warning"
                               style="margin-right: 22px;">Редактировать</a>
                        </td>
                        <td>
                            <form action="{{ route('apartments.destroy', $apartment->id) }}" method="POST"
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
        $(document).ready(function () {
            $('.popup-img').on('click', function () {
                var src = $(this).attr('src');
                var popup = `
                <div class="popup-overlay" onclick="$(this).remove()">
                    <img src="${src}" class="popup-img-expanded">
                </div>
            `;
                $('body').append(popup);
            });
        });
    </script>
@endsection
