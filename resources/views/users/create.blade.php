@extends('layouts.app')

@section('content')

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Создать пользователя</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('users.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label class="form-label" for="basic-default-fullname">Имя</label>
                    <input type="text" name="name" class="form-control"
                           id="basic-default-fullname" placeholder="Имя" required>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="basic-default-message">Email</label>
                    <input type="text" name="email" class="form-control"
                           id="basic-default-fullname" placeholder="Email" required>
                </div>
                <div class="mb-3">
                    <label for="formFile" class="form-label">Пароль</label>
                    <input class="form-control house-photo" type="password" name="password" id="formFile"
                           placeholder="Пароль">
                </div>
                <button type="submit" class="btn btn-primary">Сохранить</button>
            </form>
        </div>
    </div>

@endsection
