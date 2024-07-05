@extends('layouts.app')

@section('content')
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Создать пользователя</h5>
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
                    <label class="form-label" for="basic-default-fullname">Имя</label>
                    <input type="text" name="name" class="form-control"
                           id="basic-default-fullname" placeholder="Имя" required>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="basic-default-secondname">Фамилия</label>
                    <input type="text" name="second_name" class="form-control"
                           id="basic-default-secondname" placeholder="Фамилия" required>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="basic-default-login">Login</label>
                    <input type="text" name="login" class="form-control"
                           id="basic-default-login" placeholder="Login" required>
                </div>
                <div class="mb-3">
                    <label for="roleDropdown" class="form-label">Роль</label>
                    <div class="dropdown">
                        <button class="btn btn-default dropdown-toggle w-100 d-flex justify-content-between"
                                type="button" id="roleDropdown" data-bs-toggle="dropdown" aria-expanded="false"
                                style="border: 1px solid #d4d8dd; padding: .535rem 1.375rem .535rem .75rem;">
                            Выбрать роль
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
                <div class="mb-3">
                    <label for="formFile" class="form-label">Пароль</label>
                    <input class="form-control" type="password" name="password" id="formFile"
                           placeholder="Пароль" required>
                </div>
                <button type="submit" class="btn btn-primary">Сохранить</button>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
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
            updateDropdownSelection($('#roleDropdown'), $('#roleInput'), $(this).data('value'), $(this).text(), $('#roleDropdown').next('.dropdown-menu'));
        });

        $('#roleDropdown').on('focus', () => {
            $('#roleDropdown').css('borderColor', '#5a8dee');
        });

        $('#roleDropdown').on('blur', () => {
            $('#roleDropdown').css('borderColor', originalBorderColor);
        });
    </script>
@endsection
