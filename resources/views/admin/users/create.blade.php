@extends('admin.layouts.app')

@section('content')
    <h6 class="py-3 breadcrumb-wrapper mb-4">
        <span class="text-muted fw-light"><a class="text-muted" href="{{route('users.index')}}">{{  __('menu.Пользователи') }}</a> /</span>{{  __('user.Создать пользователя') }}
    </h6>
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">{{  __('user.Создать пользователя') }} </h5>
        </div>
        @if ($errors->any())
            <div class="alert alert-solid-danger" role="alert">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </div>
        @endif
        <div class="card-body">
            <form action="{{ route('users.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label class="form-label" for="basic-default-fullname">{{  __('user.Имя') }} *</label>
                    <input type="text" name="name" class="form-control"
                           id="basic-default-fullname" placeholder="{{  __('user.Имя') }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="basic-default-secondname">{{  __('user.Фамилия') }} *</label>
                    <input type="text" name="second_name" class="form-control"
                           id="basic-default-secondname" placeholder="{{  __('user.Фамилия') }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="basic-default-login">{{  __('user.Логин') }} *</label>
                    <input type="text" name="login" class="form-control"
                           id="basic-default-login" placeholder="{{  __('user.Логин') }}" required>
                </div>
                <div class="mb-3">
                    <label for="roleDropdown" class="form-label">{{  __('user.Роль') }} *</label>
                    <div class="dropdown">
                        <button class="btn btn-default dropdown-toggle w-100 d-flex justify-content-between"
                                type="button" id="roleDropdown" data-bs-toggle="dropdown" aria-expanded="false"
                                style="border: 1px solid #d4d8dd; padding: .535rem 1.375rem .535rem .75rem;">
                            {{  __('user.Выбрать роль') }}
                        </button>
                        <ul class="dropdown-menu w-100" aria-labelledby="roleDropdown">
                            @foreach($roles as $role)
                                <li><a class="dropdown-item" href="#"
                                       data-value="{{ $role->id }}">{{ $role->name }}</a></li>
                            @endforeach
                        </ul>
                        <input type="hidden" name="role_id" id="roleInput">
                    </div>
                </div>
                {{-- Price for coach --}}
                <div class="mb-3 price_for_coach">
                    <label for="price_for_coach" class="form-label">{{  __('user.Цена за час') }} *</label>
                    <input type="number" name="price_for_coach" id="price_for_coach" class="form-control"
                           placeholder="Цена за час">
                </div>

                {{-- Sport type --}}
                <div class="mb-3 sport_types_for_coach">
                    <label class="form-label" for="sport-types">{{  __('user.Типы спорта') }}</label>
                    <div class="select2-primary" data-select2-id="45">
                        <div class="position-relative" data-select2-id="44">
                            <select id="select2sportType"
                                    class="select2 form-select select2-hidden-accessible"
                                    name="sport_types[]"
                                    multiple=""
                                    data-select2-id="select2Primary"
                                    tabindex="-1" aria-hidden="true">
                                @foreach($sportTypes as $sportType)
                                    <option value="{{ $sportType->id }}">{{ $sportType->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Coach Desc.--}}
                <div class="mb-3 coach_desc">
                    <label class="form-label" for="stadium-description">{{  __('user.Описание') }}</label>
                    <textarea id="stadium-description" name="description" class="form-control"
                              placeholder="Описание"></textarea>
                </div>

                <div class="mb-3">
                    <label for="imageInput" class="form-label">{{  __('user.Загрузить автарку') }}</label>
                    <input type="file" name="avatar" id="imageInput" class="form-control">
                </div>
                <div class="mb-3">
                    <label for="formFile" class="form-label">{{  __('user.Пароль') }} *</label>
                    <input class="form-control" type="password" name="password" id="formFile"
                           placeholder="Пароль" required>
                </div>
                <button type="submit" class="btn btn-primary">{{  __('user.Сохранить') }}</button>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $('#select2sportType').select2({
                tags: true,
                createTag: function (params) {
                    var term = $.trim(params.term);
                    if (term === '') {
                        return null;
                    }
                    return {
                        id: term,
                        text: term,
                        newTag: true // add additional parameters
                    };
                }
            });

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

            $('#roleDropdown').next('.dropdown-menu').on('click', '.dropdown-item', function (e) {
                e.preventDefault();
                if ($(this).data('value') === 4) {
                    $('.price_for_coach').show();
                    $('.sport_types_for_coach').show();
                    $('.coach_desc').show();
                } else {
                    $('.price_for_coach').hide();
                    $('.sport_types_for_coach').hide();
                    $('.coach_desc').hide();
                    $('#price_for_coach').val(null);
                }
                updateDropdownSelection($('#roleDropdown'), $('#roleInput'), $(this).data('value'), $(this).text(), $('#roleDropdown').next('.dropdown-menu'));
            });

            $('#roleDropdown').on('focus', () => {
                $('#roleDropdown').css('borderColor', '#5a8dee');
            });

            $('#roleDropdown').on('blur', () => {
                $('#roleDropdown').css('borderColor', originalBorderColor);
            });

            $('#imageInput').on('change', function () {
                const files = Array.from($(this)[0].files);
                const imagePreview = $('#imagePreview');
                imagePreview.empty();

                files.forEach(file => {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        const imgElement = $('<img>', {
                            src: e.target.result,
                            alt: file.name,
                            class: 'uploaded-image'
                        });

                        const imgContainer = $('<div>', {class: 'image-container td__img'});
                        imgContainer.append(imgElement);

                        const deleteBtn = $('<button>', {
                            class: 'btn btn-danger btn-sm delete-image',
                            text: 'Удалить',
                            click: function () {
                                imgContainer.remove();

                                const index = files.indexOf(file);
                                if (index !== -1) {
                                    files.splice(index, 1);
                                    updateFileInput(files);
                                }
                            }
                        });
                        imgContainer.append(deleteBtn);

                        imagePreview.append(imgContainer);
                    };
                    reader.readAsDataURL(file);
                });
            });

            function updateFileInput(files) {
                const input = $('#imageInput')[0];
                const fileList = new DataTransfer();
                files.forEach(file => {
                    fileList.items.add(file);
                });
                input.files = fileList.files;
            }
        });

    </script>
@endsection
