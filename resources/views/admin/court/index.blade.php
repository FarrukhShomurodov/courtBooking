@extends('admin.layouts.app')

@section('title')
    <title>{{'Frest - '. __('court.courts') }}</title>
@endsection

@section('content')
    <div class="card">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-header">{{ __('court.courts') }}</h5>
            <a href="{{ route('courts.create') }}" class="btn btn-primary"
               style="margin-right: 22px;">{{ __('court.create') }}</a>
        </div>

        <div class="res_error"></div>
        @if ($errors->any())
            <div class="alert alert-solid-danger" role="alert">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </div>
        @endif

        <div class="card-datatable table-responsive">
            <table class="datatables-users table border-top">
                <thead>
                <tr>
                    <th>Id</th>
                    <th>{{ __('court.name') }}</th>
                    <th>{{ __('court.sport_types') }}</th>
                    <th>{{ __('court.is_active') }}</th>
                    @role('admin')
                    <th>{{ __('court.stadium') }}</th>
                    @endrole
                    <th>{{ __('court.photos') }}</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                @foreach($courts as $court)
                    <tr>
                        <td>{{ $court->id }}</td>
                        <td>
                            <a href="#" class="court-name"
                               data-bs-toggle="modal"
                               data-bs-target="#courtModal"
                               data-court-name="{{ $court->name }}"
                               data-court-description="{{ $court->description }}"
                               data-court-photos='{{ json_encode($court->photos) }}'
                            >
                                {{ $court->name }}
                            </a>
                        </td>
                        <td>{{ $court->sportTypes->name }}</td>
                        <td>
                            <label class="switch">
                                <input type="checkbox" class="switch-input" data-user-id="{{ $court->id }}"
                                       @if($court->is_active) checked @endif>
                                <span class="switch-toggle-slider">
                                    <span class="switch-on"></span>
                                    <span class="switch-off"></span>
                                </span>
                            </label>
                        </td>
                        @role('admin')
                            <td>{{ $court->stadium->name }}</td>
                        @endrole
                        <td>
                            <div class="main__td">
                                @if($court->photos)
                                    @foreach(json_decode($court->photos) as $photo)
                                        <div class="td__img">
                                            <img src="storage/{{ $photo }}" alt="Sport type photo" class="popup-img"
                                                 width="100px"/>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </td>
                        <td>
                            <div class="d-inline-block text-nowrap">
                                <button class="btn btn-sm btn-icon"
                                        onclick="location.href='{{ route('courts.edit', $court->id) }}'"><i
                                        class="bx bx-edit"></i></button>
                                <form action="{{ route('courts.destroy', $court->id) }}" method="POST"
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

    <!-- Modal -->
    <div class="modal fade" id="courtModal" tabindex="-1" aria-labelledby="courtModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="courtModalLabel"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="court-description"></div>
                    <div class="court-photos"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function () {
            $('.switch-input').on('change', function () {
                let switchInput = $(this);
                let userId = $(this).data('user-id');
                let isActive = $(this).is(':checked') ? 1 : 0;

                $.ajax({
                    url: `/api/courts/${userId}/is-active`,
                    method: 'put',
                    data: {
                        _token: '{{ csrf_token() }}',
                        is_active: isActive
                    },
                    success: function (res) {
                    },
                    error: function (error) {
                        let errors = error.responseJSON.error;
                        let errorHtml = `<div class="alert alert-solid-danger" role="alert"><li>${errors}</li></div>`;
                        $('.res_error').append(errorHtml);

                        switchInput.prop('checked', !isActive);

                        setTimeout(function () {
                            $('.res_error').html('');
                        }, 3000);
                    }
                });
            });


            $('.court-name').on('click', function () {
                let name = $(this).data('court-name');
                let description = $(this).data('court-description');

                $('#courtModalLabel').text(name);
                $('.court-description').html(description ? `<p>${description}</p>` : `<p>No description available.</p>`);


            });



            // Initialize tooltips if needed
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            $('.popup-img').on('click', function () {
                var src = $(this).attr('src');
                var popup = `
                    <div class="popup-overlay" onclick="$(this).remove()">
                        <img src="${src}" class="popup-img-expanded">
                    </div>`
            ;
            $('body').append(popup);
            });
        });
    </script>
@endsection
