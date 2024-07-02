@extends('layouts.app')

@section('content')
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Создать стадион</h5>
            <label class="switch" style="margin-right: 40px">
                <input type="checkbox" class="switch-input" name="is_active">
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
            <form id="stadiumForm" action="{{ route('stadiums.store') }}" method="POST"
                  enctype="multipart/form-data">
                @csrf

                {{--is active--}}
                <input type="number" class="is_active" name="is_active" hidden="">

                <div class="mb-3">
                    <label class="form-label" for="stadium-name">Название</label>
                    <input type="text" name="name" class="form-control" id="stadium-name"
                           placeholder="Название"
                           required>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="stadium-description">Описание</label>
                    <textarea id="stadium-description" name="description" class="form-control"
                              placeholder="Описание" required></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="stadium-address">Адрес</label>
                    <input type="text" name="address" class="form-control" id="stadium-address"
                           placeholder="Адрес"
                           required>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="stadium-map-link">Ссылка на карту</label>
                    <input type="text" name="map_link" class="form-control" id="stadium-map-link"
                           placeholder="Ссылка на карту"
                           required>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="sport-types">Виды спорта</label>
                    <div class="select2-primary" data-select2-id="45">
                        <div class="position-relative" data-select2-id="44">
                            <select id="select2Primary"
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
                    <div class="mb-3">
                        <label for="ownerDropdown" class="form-label">Владелец</label>
                        <div class="dropdown">
                            <button class="btn btn-default dropdown-toggle w-100 d-flex justify-content-between"
                                    type="button" id="ownerDropdown" data-bs-toggle="dropdown" aria-expanded="false"
                                    style="border: 1px solid #d4d8dd; padding: .535rem 1.375rem .535rem .75rem;">
                                Выбрать владельца
                            </button>
                            <ul class="dropdown-menu w-100" aria-labelledby="ownerDropdown">
                                @foreach($users as $user)
                                    <li><a class="dropdown-item" href="#"
                                           data-value="{{ $user->id }}">{{ $user->name }}</a></li>
                                @endforeach
                            </ul>
                            <input type="hidden" name="owner_id" id="ownerInput">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="coachDropdown" class="form-label">Тренер</label>
                        <div class="dropdown">
                            <button class="btn btn-default dropdown-toggle w-100 d-flex justify-content-between"
                                    type="button" id="coachDropdown" data-bs-toggle="dropdown" aria-expanded="false"
                                    style="border: 1px solid #d4d8dd; padding: .535rem 1.375rem .535рем .75рем;">
                                Выбрать тренера
                            </button>
                            <ul class="dropdown-menu w-100" aria-labelledby="coachDropdown">
                                @foreach($users as $user)
                                    <li><a class="dropdown-item" href="#"
                                           data-value="{{ $user->id }}">{{ $user->name }}</a></li>
                                @endforeach
                            </ul>
                            <input type="hidden" name="coach_id" id="coachInput">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="imageInput" class="form-label">Загрузить изображения</label>
                        <input type="file" name="photos[]" id="imageInput" class="form-control" multiple>
                    </div>
                    <div id="imagePreview" class="mb-3 main__td"></div>
                    <button type="submit" class="btn btn-primary">Сохранить</button>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function () {
            $('.switch-input').on('change', function () {
                let isActive = $(this).is(':checked') ? 1 : 0;
                $('.is_active').val(isActive);
            });
            const dropdowns = [
                {dropdown: $('#ownerDropdown'), input: $('#ownerInput')},
                {dropdown: $('#coachDropdown'), input: $('#coachInput')}
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
