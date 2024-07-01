@extends('layouts.app')

@section('content')
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Создать квартиру</h5>
        </div>
        <div class="card-body">
            <form id="apartmentForm" action="{{ route('apartments.store') }}" method="POST"
                  enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label class="form-label" for="basic-default-fullname">Название</label>
                    <input type="text" name="name" class="form-control" id="basic-default-fullname" placeholder="Название"
                           required>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="basic-default-message">Описание</label>
                    <textarea id="basic-default-message" name="description" class="form-control"
                              placeholder="Описание" required></textarea>
                </div>
                <div class="mb-3">
                    <label for="houseDropdown" class="form-label">Дом</label>
                    <div class="dropdown">
                        <button class="btn btn-default dropdown-toggle w-100 d-flex justify-content-between"
                                type="button" id="houseDropdown" data-bs-toggle="dropdown" aria-expanded="false"
                                style="border: 1px solid #d4d8dd; padding: .535rem 1.375rem .535rem .75rem;">
                            Выбрать дом
                        </button>
                        <ul class="dropdown-menu w-100" aria-labelledby="houseDropdown">
                            @foreach($houses as $house)
                                <li><a class="dropdown-item" href="#"
                                       data-value="{{ $house->id }}">{{ $house->name }}</a></li>
                            @endforeach
                        </ul>
                        <input type="hidden" name="house_id" id="houseInput">
                    </div>
                </div>
                <div class="mb-3">
                    <label for="floorDropdown" class="form-label">Этаж</label>
                    <div class="dropdown">
                        <button class="btn btn-default dropdown-toggle w-100 d-flex justify-content-between"
                                type="button" id="floorDropdown" data-bs-toggle="dropdown" aria-expanded="false"
                                style="border: 1px solid #d4d8dd; padding: .535rem 1.375rem .535rem .75rem;">
                            Выберите этаж
                        </button>
                        <ul class="dropdown-menu w-100" aria-labelledby="floorDropdown">
                            <li><a class="dropdown-item disabled" href="#">Сначала выберите дом</a></li>
                        </ul>
                        <input type="hidden" name="floor_id" id="floorInput">
                    </div>
                </div>
                <div class="mb-3">
                    <label for="imageInput" class="form-label">Загрузить изображение</label>
                    <input type="file" name="photos_url[]" id="imageInput" class="form-control" multiple>
                </div>
                <div id="imagePreview" class="mb-3 main__td">
                    <!-- Image previews will be inserted here dynamically -->
                </div>
                <button type="submit" class="btn btn-primary">Сохранить</button>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function () {
            const houseDropdown = $('#houseDropdown');
            const houseInput = $('#houseInput');
            const floorDropdown = $('#floorDropdown');
            const floorMenu = floorDropdown.next('.dropdown-menu');
            const floorInput = $('#floorInput');
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

            $('#houseDropdown + .dropdown-menu').on('click', '.dropdown-item', function (e) {
                e.preventDefault();
                updateDropdownSelection(houseDropdown, houseInput, $(this).data('value'), $(this).text(), houseDropdown.next('.dropdown-menu'));


                floorDropdown.text('Выберите этаж');
                floorInput.val('');
                floorMenu.html('<li><a class="dropdown-item disabled" href="#">Сначала выберите дом</a></li>');


                const houseId = houseInput.val();
                if (houseId) {
                    fetchFloors(houseId);
                }
            });


            houseDropdown.on('focus', () => {
                houseDropdown.css('borderColor', '#5a8dee');
            });

            floorDropdown.on('focus', () => {
                floorDropdown.css('borderColor', '#5a8dee');
            });


            houseDropdown.on('blur', () => {
                houseDropdown.css('borderColor', originalBorderColor);
            });

            floorDropdown.on('blur', () => {
                floorDropdown.css('borderColor', originalBorderColor);
            });


            function fetchFloors(houseId) {
                $.ajax({
                    url: `/api/floors-by-house/${houseId}`,
                    method: 'GET',
                    dataType: 'json',
                    success: function (floors) {
                        floorMenu.empty();
                        if (floors.length > 0) {
                            floors.forEach(floor => {
                                const floorItem = $('<a>', {
                                    class: 'dropdown-item',
                                    href: '#',
                                    text: floor.number,
                                    'data-value': floor.id
                                });
                                floorMenu.append(floorItem);
                            });

                            floorMenu.on('click', '.dropdown-item', function (e) {
                                e.preventDefault();
                                updateDropdownSelection(floorDropdown, floorInput, $(this).data('value'), $(this).text(), floorDropdown.next('.dropdown-menu'));
                            });
                        } else {
                            const noFloorsItem = $('<a>', {
                                class: 'dropdown-item disabled',
                                href: '#',
                                text: 'Этажей не найдено'
                            });
                            floorMenu.append(noFloorsItem);
                        }
                    },
                    error: function (error) {
                        console.error('Error fetching floors:', error);
                        floorMenu.html('<a class="dropdown-item disabled" href="#">Error fetching floors</a>');
                    }
                });
            }


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
