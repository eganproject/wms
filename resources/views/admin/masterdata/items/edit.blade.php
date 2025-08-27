@extends('layouts.app')
@push('styles')
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
@endpush
@push('toolbar')
    @include('layouts.partials._toolbar', [
        'title' => 'Items',
        'breadcrumbs' => ['Admin', 'Masterdata', 'Items', 'Edit Item'],
    ])
@endpush
@section('content')
    <div class="content flex-row-fluid" id="kt_content">

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Edit Item</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.masterdata.items.update', $item->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="fv-row mb-7 ">
                        <label for="product_code" class="form-label required">Product Code</label>
                        <input type="text" class="form-control form-control-solid" id="product_code" name="product_code"
                            value="{{ old('product_code', $item->product_code) }}" readonly required>
                        @error('product_code')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="fv-row mb-7 ">
                        <label for="item_category_id" class="form-label">Kategori Item</label>
                        <select class="form-select form-select-solid fw-bolder select2-hidden-accessible" id="item_category_id"
                            name="item_category_id" data-control="select2" data-placeholder="Pilih Kategori Item">
                            <option></option>
                            @foreach ($itemcategories as $category)
                                <option value="{{ $category->id }}"
                                    {{ old('item_category_id', $item->item_category_id) == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('item_category_id')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                        @error('item_category_id')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="fv-row mb-7 ">
                        <label for="sku" class="form-label required">SKU</label>
                        <input type="text" class="form-control form-control-solid" id="sku" name="sku"
                            value="{{ old('sku', $item->sku) }}" required>
                        <div id="sku-feedback" class="mt-2"></div>
                        @error('sku')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="fv-row mb-7 ">
                        <label for="nama_barang" class="form-label required">Nama Barang</label>
                        <input type="text" class="form-control form-control-solid" id="nama_barang" name="nama_barang"
                            value="{{ old('nama_barang', $item->nama_barang) }}" required>
                        @error('nama_barang')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="fv-row mb-7 ">
                        <label for="uom_id" class="form-label required">UOM</label>
                        <select class="form-select form-select-solid fw-bolder select2-hidden-accessible" id="uom_id"
                            name="uom_id" data-control="select2" data-placeholder="Pilih UOM">
                            <option></option>
                            @foreach ($uoms as $uom)
                                <option value="{{ $uom->id }}"
                                    {{ old('uom_id', $item->uom_id) == $uom->id ? 'selected' : '' }}>
                                    {{ $uom->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('uom_id')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="fv-row mb-7 ">
                        <label for="koli" class="form-label required">Koli</label>
                        <input type="number" class="form-control form-control-solid" id="koli" name="koli"
                            value="{{ old('koli', $item->koli) }}" required>
                    </div>
                    <div class="fv-row mb-7 ">
                        <label for="deskripsi" class="form-label">Deskripsi</label>
                        <textarea class="form-control form-control-solid" id="deskripsi" name="deskripsi" rows="3">{{ old('deskripsi', $item->deskripsi) }}</textarea>
                        @error('deskripsi')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>


                    <button type="submit" class="btn btn-primary mt-3">Update Item</button>
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

            var skuInput = $('#sku');
            var skuFeedback = $('#sku-feedback');
            var submitButton = $('button[type="submit"]');
            var itemId = '{{ $item->id ?? null }}'; // Get item ID for edit form

            function checkSkuUniqueness() {
                var sku = skuInput.val();
                if (sku.length === 0) {
                    skuFeedback.html('').removeClass('text-success text-danger');
                    submitButton.prop('disabled', false);
                    return;
                }

                $.ajax({
                    url: "{{ route('admin.masterdata.items.checkSkuUniqueness') }}",
                    method: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        sku: sku,
                        item_id: itemId
                    },
                    success: function(response) {
                        if (response.isUnique) {
                            skuFeedback.html('SKU tersedia.').removeClass('text-danger').addClass('text-success');
                            submitButton.prop('disabled', false);
                        } else {
                            skuFeedback.html('SKU sudah digunakan.').removeClass('text-success').addClass('text-danger');
                            submitButton.prop('disabled', true);
                        }
                    },
                    error: function() {
                        skuFeedback.html('Terjadi kesalahan saat memeriksa SKU.').removeClass('text-success').addClass('text-danger');
                        submitButton.prop('disabled', true);
                    }
                });
            }

            skuInput.on('keyup', checkSkuUniqueness);

            // Initial check if there's an old value or existing item SKU
            checkSkuUniqueness();
        });
    </script>
@endpush
