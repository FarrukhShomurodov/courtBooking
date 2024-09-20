@extends('admin.layouts.app')

@section('title')
    <title>{{'Findz - '. __('court.edit_court') }}</title>
@endsection

@section('content')
    <h6 class="py-3 breadcrumb-wrapper mb-4">
        <span class="text-muted fw-light"><a class="text-muted"
                                             href="{{route('courts.index')}}">{{  __('menu.Корт') }}</a> /</span>{{  __('court.edit_court') }}
    </h6>
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">{{  __('court.edit_court') }}</h5>
            <label class="switch" style="margin-right: 40px">
                <input type="checkbox" class="switch-input" name="is_active" @if($court->is_active) checked @endif>
                <span class="switch-toggle-slider">
                    <span class="switch-on"></span>
                    <span class="switch-off"></span>
                </span>
            </label>
        </div>
        @if ($errors->any())
            <div class="alert alert-solid-danger" role="alert">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </div>
        @endif
        <div class="card-body">
            <form id="courtForm" action="{{ route('courts.update', $court->id) }}" method="POST"
                  enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <input type="number" class="is_active" name="is_active" hidden="" value="{{ $court->is_active }}">

                <div class="mb-3">
                    <label class="form-label" for="basic-default-fullname">{{ __('court.name') }}</label>
                    <input type="text" name="name" class="form-control" id="basic-default-fullname"
                           placeholder="{{ __('court.name') }}" value="{{ $court->name }}" required>
                </div>
{{--                <div class="mb-3">--}}
{{--                    <label class="form-label" for="basic-default-message">{{ __('court.description') }}</label>--}}
{{--                    <textarea id="basic-default-message" name="description" class="form-control"--}}
{{--                              placeholder="{{ __('court.description') }}"--}}
{{--                              required>{{ $court->description }}</textarea>--}}
{{--                </div>--}}
                @role('admin')
                <div class="mb-3">
                    <label for="stadiumDropdown" class="form-label">{{ __('court.stadium') }}</label>
                    <div class="dropdown">
                        <button class="btn btn-default dropdown-toggle w-100 d-flex justify-content-between"
                                type="button" id="stadiumDropdown" data-bs-toggle="dropdown" aria-expanded="false"
                                style="border: 1px solid #d4d8dd; padding: .535rem 1.375rem .535rem .75rem;">
                            @if($court->stadium)
                                {{ $court->stadium->name }}
                            @else
                                {{ __('court.select_stadium') }}
                            @endif
                        </button>
                        <ul class="dropdown-menu w-100" aria-labelledby="stadiumDropdown">
                            @foreach($stadiums as $stadium)
                                <li><a class="dropdown-item" href="#"
                                       data-value="{{ $stadium->id }}">{{ $stadium->name }}</a></li>
                            @endforeach
                        </ul>
                        <input type="hidden" name="stadium_id" id="stadiumInput" value="{{ $court->stadium_id }}">
                    </div>
                </div>
                @endrole
                <div class="mb-3">
                    <label for="sportTypeDropdown" class="form-label">{{ __('court.sport_types') }}</label>
                    <div class="dropdown">
                        <button class="btn btn-default dropdown-toggle w-100 d-flex justify-content-between"
                                type="button" id="sportTypeDropdown" data-bs-toggle="dropdown" aria-expanded="false"
                                style="border: 1px solid #d4d8dd; padding: .535rem 1.375rem .535rem .75rem;">
                            @if(isset($court->sportTypes))
                                {{ $court->sportTypes->name }}
                            @else
                                {{ __('court.select_sport_types') }}
                            @endif
                        </button>
                        <ul class="dropdown-menu w-100" aria-labelledby="sportTypeDropdown">
                            <li><a class="dropdown-item" href="#"
                                   data-value="{{$stadium->sportTypes()->get()->first()->id }}">{{ $stadium->sportTypes()->get()->first()->name }}</a>
                            </li>
                        </ul>
                        <input type="hidden" name="sport_type_id" id="sportTypeInput"
                               value="{{ $court->sportTypes->id }}">
                    </div>
                </div>
{{--                <div class="mb-3">--}}
{{--                    <label for="imageInput" class="form-label">{{ __('court.upload_images') }}</label>--}}
{{--                    <input type="file" name="photos[]" id="imageInput" class="form-control" multiple>--}}
{{--                </div>--}}
{{--                <div id="imagePreview" class="mb-3 main__td">--}}
{{--                    @if($court->photos)--}}
{{--                        @foreach(json_decode($court->photos) as $photo)--}}
{{--                            <div class="image-container td__img" data-photo-path="{{ $photo }}">--}}
{{--                                <img src="{{ asset('storage/' . $photo) }}" alt="Court Image" class="uploaded-image">--}}
{{--                                <button type="button" class="btn btn-danger btn-sm delete-image"--}}
{{--                                        data-photo-path="{{ $photo }}"> {{ __('court.delete') }}--}}
{{--                                </button>--}}
{{--                            </div>--}}
{{--                        @endforeach--}}
{{--                    @endif--}}
{{--                </div>--}}

                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ __('court.schedule') }}</h5>
                </div>
                <div class="mb-3">
                    <table class="table">
                        <thead>
                        <tr>
                            <th>{{ __('court.time') }}</th>
                            <th>{{ __('court.cost') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @for ($i = 0; $i < 24; $i++)
                            @php
                                $schedule = $court->schedules()->firstWhere('start_time', sprintf('%02d:00', $i));
                            @endphp
                            <tr>
                                <td>{{ sprintf('%02d:00 - %02d:00', $i, ($i + 1) % 24) }}</td>
                                <td><input type="number" name="schedule[{{ $i }}][cost]" class="form-control"
                                           placeholder="Стоимость" value="{{ $schedule ? $schedule->cost / 1000: '0' }}"></td>
                                <input type="hidden" name="schedule[{{ $i }}][start_time]"
                                       value="{{ sprintf('%02d:00', $i) }}">
                                <input type="hidden" name="schedule[{{ $i }}][end_time]"
                                       value="{{ sprintf('%02d:00', ($i + 1) % 24) }}">
                            </tr>
                        @endfor
                        </tbody>
                    </table>
                </div>
                <button type="submit" class="btn btn-warning">{{ __('court.edit') }}</button>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function () {
            @if($court->is_active)
            $('.is_active').val(1);
            @else
            $('.is_active').val(1);
            @endif

            $('.switch-input').on('change', function () {
                let isActive = $(this).is(':checked') ? 1 : 0;
                $('.is_active').val(isActive);
            });

            fetchSportTypes($('#stadiumInput').val())

            const dropdowns = [
                {dropdown: $('#stadiumDropdown'), input: $('#stadiumInput')},
                {dropdown: $('#sportTypeDropdown'), input: $('#sportTypeInput')}
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

            function fetchSportTypes(stadiumId) {
                $.ajax({
                    url: `/api/stadium-spot-type/${stadiumId}`,
                    method: 'GET',
                    success: function (response) {
                        let options = '';
                        response.forEach(function (sportType) {
                            if (sportType.id !== {{ $court->sportTypes->id }}) {
                                options += `<li><a class="dropdown-item" href="#" data-value="${sportType.id}">${sportType.name}</a></li>`;
                            }
                        });
                        $('#sportTypeDropdown').next('.dropdown-menu').append(options);
                        if (sportType.id !== {{ $court->sportTypes->id }}) {
                            $('#sportTypeDropdown').append('Выбрать Тип Спорта');
                        }
                        $('#sportTypeInput').val('');
                    },
                    error: function (err) {
                        console.log(err);
                    }
                });
            }

            // Event listener for stadium dropdown change
            $('#stadiumDropdown').next('.dropdown-menu').on('click', '.dropdown-item', function () {
                const selectedStadiumId = $(this).data('value');
                fetchSportTypes(selectedStadiumId);
            });

            // Handle new image uploads
        {{--    $('#imageInput').on('change', function () {--}}
        {{--        const files = Array.from($(this)[0].files);--}}
        {{--        const imagePreview = $('#imagePreview');--}}

        {{--        files.forEach(file => {--}}
        {{--            const reader = new FileReader();--}}
        {{--            reader.onload = function (e) {--}}
        {{--                const imgElement = $('<img>', {--}}
        {{--                    src: e.target.result,--}}
        {{--                    alt: file.name,--}}
        {{--                    class: 'uploaded-image'--}}
        {{--                });--}}

        {{--                const imgContainer = $('<div>', {class: 'image-container td__img'});--}}
        {{--                imgContainer.append(imgElement);--}}

        {{--                const deleteBtn = $('<button>', {--}}
        {{--                    class: 'btn btn-danger btn-sm delete-image',--}}
        {{--                    text: 'Удалить',--}}
        {{--                    click: function () {--}}
        {{--                        imgContainer.remove();--}}
        {{--                        const index = files.indexOf(file);--}}
        {{--                        if (index !== -1) {--}}
        {{--                            files.splice(index, 1);--}}
        {{--                            updateFileInput(files);--}}
        {{--                        }--}}
        {{--                    }--}}
        {{--                });--}}
        {{--                imgContainer.append(deleteBtn);--}}

        {{--                imagePreview.append(imgContainer);--}}
        {{--            };--}}
        {{--            reader.readAsDataURL(file);--}}
        {{--        });--}}

        {{--        function updateFileInput(files) {--}}
        {{--            const input = $('#imageInput')[0];--}}
        {{--            const fileList = new DataTransfer();--}}
        {{--            files.forEach(file => {--}}
        {{--                fileList.items.add(file);--}}
        {{--            });--}}
        {{--            input.files = fileList.files;--}}
        {{--        }--}}
        {{--    });--}}


        {{--    $(document).on('click', '.delete-image', function () {--}}
        {{--        const path = $(this).data('photo-path');--}}
        {{--        if (path) {--}}
        {{--            $.ajax({--}}
        {{--                url: `/api/delete/${path}/{{ $court->id }}`,--}}
        {{--                method: 'DELETE',--}}
        {{--                data: {--}}
        {{--                    _token: '{{ csrf_token() }}'--}}
        {{--                },--}}
        {{--                success: function (res) {--}}
        {{--                    console.log(res);--}}
        {{--                    $(this).closest('.image-container').remove();--}}
        {{--                }.bind(this),--}}
        {{--                error: function (error) {--}}
        {{--                    console.error('Error deleting photo:', error);--}}
        {{--                }--}}
        {{--            });--}}
        {{--        }--}}
        {{--    });--}}
        {{--});--}}
    </script>
@endsection
