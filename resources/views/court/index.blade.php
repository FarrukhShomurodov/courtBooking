@extends('layouts.app')

@section('content')
    <div class="card">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-header">Корты</h5>
            <a href="{{ route('courts.create') }}" class="btn btn-primary" style="margin-right: 22px;">Создать</a>
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
                    <th>Is Active</th>
                    <th>Стадион</th>
                    <th>Фото</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>

                @foreach($courts as $court)
                    <tr>
                        <td>{{ $court->id }}</td>
                        <td>{{ $court->name }}</td>
                        <td>{{ $court->description }}</td>
                        <td>
                            <label class="switch">
                                <input type="checkbox" class="switch-input" data-user-id="{{ $court->id }}"
                                       @if($court->is_active) checked @endif>
                                <span class="switch-toggle-slider">
                                    <span class="switch-on"></span>
                                    <span class="switch-off"></span>
                                </span>
                            </label>
                        </td>
                        <td>{{ $court->stadium->name }}</td>
                        <td>
                            <div class="main__td">
                                @if($court->photos)
                                    @foreach(json_decode($court->photos) as $photo)
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
                                        onclick="location.href='{{ route('courts.edit', $court->id) }}'"><i
                                        class="bx bx-edit"></i></button>
                                <form action="{{ route('courts.destroy', $court->id) }}" method="POST"
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

            $('.switch-input').on('change', function () {
                let userId = $(this).data('user-id');
                let isActive = $(this).is(':checked') ? 1 : 0;

                $.ajax({
                    url: `/api/courts/${userId}/is-active`,
                    method: 'put',
                    data: {
                        _token: '{{ csrf_token() }}',
                        is_active: isActive
                    },
                    success: function (res) {
                    },
                    error: function (error) {
                    }
                });
            });

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
