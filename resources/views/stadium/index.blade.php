@extends('layouts.app')

@section('content')
    <div class="card">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-header">Стадион</h5>
            <a href="{{ route('stadiums.create') }}" class="btn btn-primary" style="margin-right: 22px;">Создать</a>
        </div>
        @if ($errors->any())
            <div class="alert alert-solid-danger" role="alert">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </div>
        @endif

        <div class="card-datatable table-responsive">
            <table class="datatables-users table border-top">
                <thead>
                <tr>
                    <th>Id</th>
                    <th>Название</th>
                    <th>Описание</th>
                    <th>Адресс</th>
                    <th>Ссылка на карту</th>
                    <th>Тренер</th>
                    <th>Владелец</th>
                    <th>Вид спорта</th>
                    <th>Фото</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>

                @foreach($stadiums as $stadium)
                    <tr>
                        <td>{{ $stadium->id }}</td>
                        <td>{{ $stadium->name }}</td>
                        <td>{{ $stadium->description }}</td>
                        <td>{{ $stadium->address }}</td>
                        <td>{{ $stadium->map_link }}</td>
                        <td>{{ $stadium->coach->name }}</td>
                        <td>{{ $stadium->owner->name }}</td>
                        <td>
                            @foreach($stadium->sportTypes as $sportType)
                                <span>{{$sportType->name}} {{ count($stadium->sportTypes) > 1 ? ',' : '' }}</span><br>
                            @endforeach
                        </td>
                        <td>
                            <div class="main__td">
                                @if($stadium->photos)
                                    @foreach(json_decode($stadium->photos) as $photo)
                                        <div class="td__img">
                                            <img src="storage/{{ $photo }}" alt="Sport type photo" class="popup-img"
                                                 width="100px"/>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </td>
                        <td>
                            <div class="d-inline-block text-nowrap">
                                <button class="btn btn-sm btn-icon"
                                        onclick="location.href='{{ route('stadiums.edit', $stadium->id) }}'"><i
                                        class="bx bx-edit"></i></button>
                                <form action="{{ route('stadiums.destroy', $stadium->id) }}" method="POST"
                                      style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-icon delete-record"><i class="bx bx-trash"></i>
                                    </button>
                                </form>
                            </div>
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
