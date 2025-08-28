@extends('layouts.app')
@push('styles')
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
@endpush
@push('toolbar')
    @include('layouts.partials._toolbar', [
        'title' => 'Users',
        'breadcrumbs' => ['Admin', 'Masterdata', 'Users', 'Tambah Users'],
    ])
@endpush
@section('content')
    <div class="content flex-row-fluid" id="kt_content">
        @if ($errors->any())
            @php
                dump($errors);
            @endphp
        @endif
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Tambah User</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.masterdata.users.store') }}" method="POST">
                    @csrf
                    <div class="fv-row mb-3 ">
                        <label for="name" class="form-label required">Nama</label>
                        <input type="text" class="form-control form-control-solid" id="name" name="name"
                            value="{{ old('name') }}" required>
                       
                    </div>
                    <div class="fv-row mb-3 ">
                        <label for="email" class="form-label required">Email</label>
                        <input type="email" class="form-control form-control-solid" id="email" name="email"
                            value="{{ old('email') }}" required>
                      
                    </div>
                    <div class="fv-row mb-3 ">
                        <label for="password" class="form-label required">Password</label>
                        <input type="password" class="form-control form-control-solid" id="password" name="password"
                            required>
                    </div>
                    <div class="fv-row mb-3 ">
                        <label for="jabatan_id" class="form-label required">Jabatan</label>
                        <select class="form-select form-select-solid fw-bolder select2-hidden-accessible" id="jabatan_id"
                            name="jabatan_id" data-control="select2" data-placeholder="Pilih opsi">
                            <option></option>
                            @foreach ($jabatans as $jabatan)
                                <option value="{{ $jabatan->id }}">{{ $jabatan->name }}</option>
                            @endforeach
                        </select>
                       
                    </div>
                    <button type="submit" class="btn btn-primary mt-3">Submit</button>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            toastr.options = {
                "closeButton": true,
                "debug": false,
                "newestOnTop": false,
                "progressBar": true,
                "positionClass": "toast-top-center",
                "preventDuplicates": false,
                "showDuration": "300",
                "hideDuration": "1000",
                "timeOut": "5000",
                "extendedTimeOut": "1000",
                "showEasing": "swing",
                "hideEasing": "linear",
                "showMethod": "fadeIn",
                "hideMethod": "fadeOut"
            };

            @if (Session::has('success'))
                toastr.success("{{ session('success') }}");
            @endif

            @if (Session::has('error'))
                toastr.error("{{ session('error') }}");
            @endif
        });
    </script>
@endpush
