@extends('layouts.app')
@push('toolbar')
    @include('layouts.partials._toolbar', [
        'title' => 'Users',
        'breadcrumbs' => ['Admin', 'Masterdata', 'Users', 'Tambah Users'],
    ])
@endpush
@section('content')
    <div class="content flex-row-fluid" id="kt_content">

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Tambah User</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('users.store') }}" method="POST">
                    @csrf
                    <div class="fv-row mb-3 ">
                        <label for="name" class="form-label required">Nama</label>
                        <input type="text" class="form-control form-control-solid" id="name" name="name"
                            required>
                    </div>
                    <div class="fv-row mb-3 ">
                        <label for="email" class="form-label required">Email</label>
                        <input type="email" class="form-control form-control-solid" id="email" name="email"
                            required>
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
