@extends('layouts.app')
@push('styles')
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
@endpush
@push('toolbar')
    @include('layouts.partials._toolbar', [
        'title' => 'Items',
        'breadcrumbs' => ['Admin', 'Masterdata', 'Items', 'Tambah Item'],
    ])
@endpush
@section('content')
    <div class="content flex-row-fluid" id="kt_content">

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Tambah Item</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.masterdata.items.store') }}" method="POST">
                    @csrf
                    <div class="fv-row mb-3 ">
                        <label for="product_code" class="form-label required">Product Code</label>
                        <input type="text" class="form-control form-control-solid" id="product_code" name="product_code"
                            value="{{ old('product_code', $generatedProductCode) }}" readonly required>
                        @error('product_code')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                      <div class="fv-row mb-3 ">
                        <label for="sku" class="form-label required">SKU</label>
                        <input type="text" class="form-control form-control-solid" id="sku" name="sku"
                            value="{{ old('sku') }}" required>
                        @error('sku')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="fv-row mb-3 ">
                        <label for="nama_barang" class="form-label required">Nama Barang</label>
                        <input type="text" class="form-control form-control-solid" id="nama_barang" name="nama_barang"
                            value="{{ old('nama_barang') }}" required>
                        @error('nama_barang')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="fv-row mb-3 ">
                        <label for="uom_id" class="form-label required">UOM</label>
                        <select class="form-select form-select-solid fw-bolder select2-hidden-accessible" id="uom_id"
                            name="uom_id" data-control="select2" data-placeholder="Pilih UOM">
                            <option></option>
                            @foreach ($uoms as $uom)
                                <option value="{{ $uom->id }}" {{ old('uom_id') == $uom->id ? 'selected' : '' }}>
                                    {{ $uom->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('uom_id')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="fv-row mb-3 ">
                        <label for="koli" class="form-label required">Koli</label>
                        <input type="number" class="form-control form-control-solid" id="koli" name="koli"
                            required>
                    </div>
                    <div class="fv-row mb-3 ">
                        <label for="deskripsi" class="form-label">Deskripsi</label>
                        <textarea class="form-control form-control-solid" id="deskripsi" name="deskripsi" rows="3">{{ old('deskripsi') }}</textarea>
                        @error('deskripsi')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>   
                    
                    <button type="submit" class="btn btn-primary mt-3">Tambah Item</button>
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
