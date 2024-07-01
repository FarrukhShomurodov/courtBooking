@extends('layouts.app')

@section('content')

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Создать Дом</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('houses.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label class="form-label" for="basic-default-fullname">Название</label>
                    <input type="text" name="name" class="form-control"
                           id="basic-default-fullname" placeholder="Название" required>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="basic-default-message">Описание</label>
                    <textarea id="basic-default-message" name="description"
                              class="form-control" placeholder="Описание" data-gramm="false" wt-ignore-input="true"
                              required></textarea>
                </div>
                <div class="mb-3">
                    <label for="formFile" class="form-label">Фото</label>
                    <input class="form-control house-photo" type="file" name="photo_url" id="formFile">
                </div>
                <button type="submit" class="btn btn-primary">Сохранить</button>
            </form>
        </div>
    </div>

@endsection
