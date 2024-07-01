@extends('layouts.app')

@section('content')
        <div class="card">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-header">Пользователи</h5>
                <a href="{{ route('users.create') }}" class="btn btn-primary" style="margin-right: 22px;">Создать</a>
            </div>
            <div class="card-datatable table-responsive">
                <table class="datatables-users table border-top">
                    <thead>
                    <tr>
                        <th>Id</th>
                        <th>Имя</th>
                        <th>Электроная почта</th>
                        <th></th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($users as $user)
                        <tr>
                            <td>{{ $user->id }}</td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
                                <a href="{{ route('users.edit', $user->id) }}" class="btn btn-warning"
                                   style="margin-right: 22px;">Редактировать</a>
                            </td>
                            <td>

                                <form action="{{ route('users.destroy', $user->id) }}" method="POST"
                                      style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger" style="margin-left: -40px !important;">Удалить</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
@endsection
