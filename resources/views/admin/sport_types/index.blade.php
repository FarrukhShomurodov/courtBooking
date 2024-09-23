@extends('admin.layouts.app')

@section('title')
    <title>{{'Findz - '. __('sportType.sport_types') }}</title>
@endsection

@section('content')
    <div class="card">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-header">@lang('sportType.sport_types')</h5>
            <a href="{{ route('sport-types.create') }}" class="btn btn-primary"
               style="margin-right: 22px;">@lang('sportType.create')</a>
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
                    <th>@lang('sportType.id')</th>
                    <th>@lang('sportType.name')</th>
{{--                    <th>@lang('sportType.photo')</th>--}}
                    <th></th>
                </tr>
                </thead>
                <tbody>

                @foreach($spotTypes as $spotType)
                    <tr>
                        <td>{{ $spotType->id }}</td>
                        <td>{{ strlen($spotType->name) > 30 ? substr($spotType->name, 0, 30) . "..."  : $spotType->name }}</td>
{{--                        <td>--}}
{{--                            <div class="main__td">--}}
{{--                                @if($spotType->photos)--}}
{{--                                    @foreach(json_decode($spotType->photos) as $photo)--}}
{{--                                        <div class="td__img">--}}
{{--                                            <img src="storage/{{ $photo }}" alt="@lang('sportType.sport_type_photo')"--}}
{{--                                                 class="popup-img"--}}
{{--                                                 width="100px"/>--}}
{{--                                        </div>--}}
{{--                                    @endforeach--}}
{{--                                @endif--}}
{{--                            </div>--}}
{{--                        </td>--}}
                        <td>
                            <div class="d-inline-block text-nowrap">
                                <button class="btn btn-sm btn-icon"
                                        onclick="location.href='{{ route('sport-types.edit', $spotType->id) }}'"><i
                                        class="bx bx-edit"></i></button>
                                <form action="{{ route('sport-types.destroy', $spotType->id) }}" method="POST"
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
