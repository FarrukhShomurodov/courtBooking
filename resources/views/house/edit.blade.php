@extends('layouts.app')

@section('content')
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Редактировать дом</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('houses.update', $house) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('put')
                <div class="mb-3">
                    <label class="form-label" for="basic-default-fullname">Название</label>
                    <input type="text" value="{{ $house->name }}" name="name" class="form-control"
                           id="basic-default-fullname" placeholder="Название" required>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="basic-default-message">Описание</label>
                    <textarea id="basic-default-message" name="description"
                              class="form-control" placeholder="Описание" data-gramm="false" wt-ignore-input="true"
                              required>{{ $house->description }}</textarea>
                </div>
                <div class="mb-3">
                    <label for="formFile" class="form-label">Фото</label>
                    <input class="form-control house-photo" type="file" name="photo_url" id="formFile">
                </div>

                <button type="submit" class="btn btn-primary">Редактировать</button>
            </form>
        </div>
    </div>

@endsection
