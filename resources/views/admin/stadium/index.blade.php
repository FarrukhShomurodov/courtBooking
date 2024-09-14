@extends('admin.layouts.app')

@section('title')
    <title>{{'Frest - '. __('stadium.stadium') }}</title>
@endsection


@section('content')
    <div class="card">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-header">@lang('stadium.stadium')</h5>
            @can('manage stadiums')
                <a href="{{ route('stadiums.create') }}" class="btn btn-primary" style="margin-right: 22px;">@lang('stadium.create')</a>
            @endcan
        </div>

        <div class="res_error"></div>
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
                    <th>@lang('stadium.id')</th>
                    <th>@lang('stadium.name')</th>
                    <th>@lang('stadium.address')</th>
                    <th>@lang('stadium.map_link')</th>
                    <th>@lang('stadium.coach')</th>
                    <th>@lang('stadium.owner')</th>
                    <th>@lang('stadium.manager')</th>
                    <th>@lang('stadium.sport_types')</th>
                    <th>@lang('stadium.is_active')</th>
                    <th>@lang('stadium.photos')</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>

                @foreach($stadiums as $stadium)
                    <tr>
                        <td>{{ $stadium->id }}</td>
                        <td>{{ $stadium->name }}</td>
                        <td>{{ $stadium->address }}</td>
                        <td>{{ $stadium->map_link }}</td>
                        <td>{{ $stadium->coach->user->name ?? '' }}</td>
                        <td>{{ $stadium->owner->name }}</td>
                        <td>{{ $stadium->manager->name }}</td>
                        <td>
                            @foreach($stadium->sportTypes as $sportType)
                                <span>{{ $sportType->name }} {{ count($stadium->sportTypes) !== $sportType->id ? (count($stadium->sportTypes) > 1 ? ',' : ''): '' }}</span><br>
                            @endforeach
                        </td>
                        <td>
                            <label class="switch">
                                <input type="checkbox" class="switch-input" data-user-id="{{ $stadium->id }}"
                                       @if($stadium->is_active) checked @endif>
                                <span class="switch-toggle-slider">
                                    <span class="switch-on"></span>
                                    <span class="switch-off"></span>
                                </span>
                            </label>
                        </td>
                        <td>
                            <div class="main__td">
                                @if($stadium->photos)
                                    @foreach(json_decode($stadium->photos) as $photo)
                                        <div class="td__img">
                                            <img src="storage/{{ $photo }}" alt="@lang('stadium.sport_type_photo')" class="popup-img"
                                                 width="100px"/>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </td>
                        @can('manage stadiums')
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
                        @endcan
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
                let src = $(this).attr('src');
                let popup = `
                <div class="popup-overlay" onclick="$(this).remove()">
                    <img src="${src}" class="popup-img-expanded">
                </div>
            `;
                $('body').append(popup);
            });

            $('.switch-input').on('change', function () {
                let switchInput = $(this);
                let stadiumId = $(this).data('user-id');
                let isActive = $(this).is(':checked') ? 1 : 0;

                $.ajax({
                    url: `/api/stadium/${stadiumId}/is-active`,
                    method: 'put',
                    data: {
                        _token: '{{ csrf_token() }}',
                        is_active: isActive
                    },
                    success: function (res) {
                        $('.res_error').html('');
                    },
                    error: function (error) {
                        let errors = error.responseJSON.error;
                        let errorHtml = `<div class="alert alert-solid-danger" role="alert"><li>${errors}</li></div>`;
                        $('.res_error').append(errorHtml);

                        switchInput.prop('checked', !isActive);

                        setTimeout(function () {
                            $('.res_error').html('');
                        }, 3000);
                    }
                });
            });
        });
    </script>
@endsection
