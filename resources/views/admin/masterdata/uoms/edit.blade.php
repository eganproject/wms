@extends('layouts.app')
@push('styles')
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
@endpush
@push('toolbar')
    @include('layouts.partials._toolbar', [
        'title' => 'UOMs',
        'breadcrumbs' => ['Admin', 'Masterdata', 'UOMs', 'Ubah UOM'],
    ])
@endpush
@section('content')
    <div class="content flex-row-fluid" id="kt_content">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Edit UOM</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('masterdata.uoms.update', $uom->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="fv-row mb-3">
                        <label for="name" class="form-label required">Nama UOM</label>
                        <input type="text" class="form-control form-control-solid @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $uom->name) }}"
                            required>
                        @error('name')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                    <div class="fv-row mb-3">
                        <label for="description" class="form-label">Deskripsi</label>
                        <textarea class="form-control form-control-solid @error('description') is-invalid @enderror" id="description" name="description">{{ old('description', $uom->description) }}</textarea>
                        @error('description')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-primary mt-3">Update</button>
                    <a href="{{ route('masterdata.uoms.index') }}" class="btn btn-secondary mt-3">Batal</a>
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