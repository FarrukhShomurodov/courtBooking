@extends('admin.layouts.app')

@section('title')
    <title>{{'Findz - '. __('user.Пользователи') }}</title>
@endsection

@section('content')
    <div class="card">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-header">{{  __('user.Пользователи') }}</h5>
            <a href="{{ route('users.create') }}" class="btn btn-primary "
               style="margin-right: 22px;">{{  __('user.Создать') }}</a>
        </div>
        <div class="card-datatable table-responsive">
            <table class="datatables-users table border-top">
                <thead>
                <tr>
                    <th>Id</th>
                    <th>{{  __('user.Имя') }}</th>
                    <th>{{  __('user.Фамилия') }}</th>
{{--                    <th>{{  __('user.Аватар') }}</th>--}}
                    <th>{{  __('user.Логин') }}</th>
                    <th>{{  __('user.Роль') }}</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                @php
                    $count = 1
                @endphp

                @foreach($users as $user)
                    <tr>
                        <td>{{ $count++ }}</td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->second_name }}</td>
{{--                        <td>--}}
{{--                            @if($user->avatar)--}}
{{--                                <img class="rounded-circle"--}}
{{--                                     src="{{ \Illuminate\Support\Facades\Storage::url($user->avatar) }}" width="50px"--}}
{{--                                     height="50px">--}}
{{--                            @endif--}}
{{--                        </td>--}}
                        <td>{{ $user->login }}</td>
                        <td>
                            @php
                                switch ($user->roles->pluck('name')[0]){
                                      case 'stadium manager':
                                         echo "Администратор стадиона";
                                          break;
                                      case 'admin':
                                      echo"Findz";
                                          break;
                                      case 'owner stadium':
                                      echo "Владелец стадиона";
                                          break;
                                      case 'trainer':
                                        echo "Тренер";
                                          break;
                                }
                            @endphp
                        </td>
                        <td>
                            <div class="d-inline-block text-nowrap">
                                <button class="btn btn-sm btn-icon"
                                        onclick="location.href='{{ route('users.edit', $user->id) }}'"><i
                                        class="bx bx-edit"></i></button>
                                <form action="{{ route('users.destroy', $user->id) }}" method="POST"
                                      style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-icon delete-record"><i class="bx bx-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
