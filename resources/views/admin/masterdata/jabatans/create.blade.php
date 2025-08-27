@extends('layouts.app')
@push('toolbar')
    @include('layouts.partials._toolbar', [
        'title' => 'Jabatans',
        'breadcrumbs' => ['Admin', 'Masterdata', 'Jabatans', 'Tambah Jabatan'],
    ])
@endpush
@section('content')
    <div class="content flex-row-fluid" id="kt_content">

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Tambah Jabatan</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('jabatans.store') }}" method="POST">
                    @csrf
                    <div class="fv-row mb-3 ">
                        <label for="name" class="form-label required">Name</label>
                        <input type="text" class="form-control form-control-solid" id="name" name="name"
                            required>
                    </div>
                    <div class="fv-row mb-3 ">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control form-control-solid" id="description" name="description"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary mt-3">Submit</button>
                </form>
            </div>
        </div>
    </div>
@endsection