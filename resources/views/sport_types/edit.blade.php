@extends('layouts.app')

@section('content')
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Редактировать тип спорта</h5>
        </div>
        <div class="card-body">
            <form id="apartmentForm" action="{{ route('sport-types.update', $sportType->id) }}" method="POST"
                  enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label class="form-label" for="basic-default-fullname">Название</label>
                    <input type="text" name="name" class="form-control" id="basic-default-fullname"
                           placeholder="Название"
                           value="{{ $sportType->name }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="basic-default-message">Описание</label>
                    <textarea id="basic-default-message" name="description" class="form-control"
                              placeholder="Описание" required>{{ $sportType->description }}</textarea>
                </div>
                <div class="mb-3">
                    <label for="imageInput" class="form-label">Загрузить изображение</label>
                    <input type="file" name="photos[]" id="imageInput" class="form-control" multiple>
                </div>
                <div id="imagePreview" class="mb-3 main__td">
                    @if($sportType->photos)
                        @foreach(json_decode($sportType->photos) as $photo)
                            <div class="image-container td__img" data-photo-path="{{ $photo }}">
                                <img src="{{ asset('storage/' . $photo) }}" alt="Apartment Image"
                                     class="uploaded-image">
                                <button type="button" class="btn btn-danger btn-sm delete-image"
                                        data-photo-path="{{ $photo }}">Удалить
                                </button>
                            </div>
                        @endforeach
                    @endif
                </div>
                <button type="submit" class="btn btn-primary">Редактировать</button>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function () {
            $('#imageInput').on('change', function () {
                const files = Array.from($(this)[0].files);
                const imagePreview = $('#imagePreview');

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

                function updateFileInput(files) {
                    const input = $('#imageInput')[0];
                    const fileList = new DataTransfer();
                    files.forEach(file => {
                        fileList.items.add(file);
                    });
                    input.files = fileList.files;
                }
            });


            $(document).on('click', '.delete-image', function () {
                const path = $(this).data('photo-path');
                if (path) {
                    $.ajax({
                        url: `/api/delete/${path}/{{ $sportType->id }}`,
                        method: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function (res) {
                            console.log(res);
                            $(this).closest('.image-container').remove();
                        }.bind(this),
                        error: function (error) {
                            console.error('Error deleting photo:', error);
                        }
                    });
                }
            });
        });
    </script>
@endsection
