@extends('layouts.app')

@section('content')
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Создать пользователя</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('users.update', $user->id) }}" method="POST">
                @csrf
                @method('put')
                <div class="mb-3">
                    <label class="form-label" for="basic-default-fullname">Имя</label>
                    <input type="text" name="name" class="form-control"
                           id="basic-default-fullname" placeholder="Имя" value="{{ $user->name }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="basic-default-message">Email</label>
                    <input type="text" name="email" class="form-control" value="{{ $user->email }}"
                           id="basic-default-fullname" placeholder="Email" required>
                </div>
                <div class="mb-3">
                    <label for="formFile" class="form-label">Пароль</label>
                    <input class="form-control house-photo" type="password" name="password" id="formFile"
                           placeholder="Пароль">
                </div>
                <button type="submit" class="btn btn-primary">Редактировать</button>
            </form>
        </div>
    </div>

@endsection
