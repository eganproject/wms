@extends('layouts.app')

@push('toolbar')
    @include('layouts.partials._toolbar', [
        'title' => 'Tambah Penerimaan Barang',
        'breadcrumbs' => ['Admin', 'Stok Masuk', 'Tambah Penerimaan Barang'],
    ])
@endpush

@section('content')
    <div class="content flex-row-fluid" id="kt_content">
        <div class="card">
            <div class="card-body">
                <form id="penerimaan-form" action="{{ route('admin.stok-masuk.daftar-penerimaan-barang.store') }}"
                    method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 mb-5">
                            <label class="form-label fs-6 fw-bolder text-dark">Kode Dokumen</label>
                            <input type="text" name="code" class="form-control form-control-solid"
                                value="{{ $newCode }}" readonly>
                        </div>
                        <div class="col-md-6 mb-5">
                            <label class="form-label fs-6 fw-bolder text-dark">Tanggal</label>
                            <input type="text" name="date"
                                class="form-control form-control-solid flatpickr-input @error('date') is-invalid @enderror"
                                value="{{ old('date', date('Y-m-d')) }}" required>
                            @error('date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-5">
                            <label class="form-label fs-6 fw-bolder text-dark">Gudang</label>
                            <select name="warehouse_id"
                                class="form-select form-select-solid @error('warehouse_id') is-invalid @enderror"
                                data-control="select2" data-placeholder="Pilih Gudang" required>
                                <option></option>
                                @foreach ($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}"
                                        {{ old('warehouse_id') == $warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('warehouse_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-5">
                            <label class="form-label fs-6 fw-bolder text-dark">Deskripsi</label>
                            <textarea name="description" class="form-control form-control-solid">{{ old('description') }}</textarea>
                        </div>
                    </div>

                    <h3 class="mt-5">Item</h3>
                    <table class="table table-bordered" id="items-table">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th width="150px">Quantity</th>
                                <th width="150px">Koli</th>
                                <th width="50px">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- Baris item akan ditambahkan oleh javascript --}}
                        </tbody>
                    </table>
                    <button type="button" class="btn btn-primary btn-sm" id="add-item-btn">Tambah Item</button>

                    <div class="d-flex justify-content-end mt-10">
                        <a href="{{ route('admin.stok-masuk.daftar-penerimaan-barang.index') }}"
                            class="btn btn-light me-3">Batal</a>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        var itemsData = @json($items);

        $(document).ready(function() {
            $(".flatpickr-input").flatpickr({
                dateFormat: "Y-m-d",
            });

            let itemIndex = 0;

            function initializeSelect2(selector) {
                selector.select2({
                    placeholder: "Pilih Item",
                });
            }

            function addNewRow() {
                let newRowHtml = `
                    <tr data-index="${itemIndex}">
                        <td>
                            <select name="items[${itemIndex}][item_id]" class="form-select item-select" required></select>
                        </td>
                        <td><input type="number" name="items[${itemIndex}][quantity]" class="form-control quantity-input" value="1" min="1" required></td>
                        <td><input type="number" name="items[${itemIndex}][koli]" class="form-control koli-input" value="0" min="0" step="any"></td>
                        <td><button type="button" class="btn btn-danger btn-sm remove-item-btn">X</button></td>
                    </tr>`;

                $('#items-table tbody').append(newRowHtml);

                let newSelect = $('tr[data-index="' + itemIndex + '"] .item-select');

                // Tambahkan placeholder option
                newSelect.append(new Option('', '', true, true));

                // Populate dropdown dengan data item dari JSON
                itemsData.forEach(function(item) {
                    let option = new Option(item.nama_barang, item.id, false, false);
                    $(option).attr('data-koli', item.koli || 1);
                    newSelect.append(option);
                });

                newSelect.val(null).trigger('change');

                initializeSelect2(newSelect);
                itemIndex++;
            }

            $('#add-item-btn').click(function() {
                addNewRow();
            });

            $('#items-table').on('click', '.remove-item-btn', function() {
                $(this).closest('tr').remove();
            });

            // Kalkulasi otomatis Quantity -> Koli
            $('#items-table').on('input change', '.quantity-input, .item-select', function() {
                let row = $(this).closest('tr');
                let quantity = parseFloat(row.find('.quantity-input').val()) || 0;
                let productKoli = parseFloat(row.find('.item-select option:selected').data('koli')) || 1;

                if (productKoli > 0) {
                    let calculatedKoli = quantity / productKoli;
                    row.find('.koli-input').val(calculatedKoli.toFixed(2));
                }
            });

            // Kalkulasi otomatis Koli -> Quantity
            $('#items-table').on('input', '.koli-input', function() {
                let row = $(this).closest('tr');
                let koli = parseFloat($(this).val()) || 0;
                let productKoli = parseFloat(row.find('.item-select option:selected').data('koli')) || 1;

                let calculatedQuantity = koli * productKoli;
                row.find('.quantity-input').val(calculatedQuantity);
            });

            // Tambah baris pertama secara default
            addNewRow();

            // SweetAlert for form submission
            $('#penerimaan-form').on('submit', function(e) {
                e.preventDefault(); // Prevent default form submission

                var form = $(this);
                var url = form.attr('action');
                var method = form.attr('method');
                var data = form.serialize();

                // Clear previous errors
                $('.is-invalid').removeClass('is-invalid');
                $('.invalid-feedback').remove();

                Swal.fire({
                    text: "Apakah Anda yakin ingin menyimpan data penerimaan barang ini?",
                    icon: "question",
                    showCancelButton: true,
                    buttonsStyling: false,
                    confirmButtonText: "Ya, Simpan!",
                    cancelButtonText: "Tidak, Batalkan",
                    customClass: {
                        confirmButton: "btn fw-bold btn-primary",
                        cancelButton: "btn fw-bold btn-active-light-primary"
                    }
                }).then(function(result) {
                    if (result.value) {
                        $.ajax({
                            url: url,
                            type: method,
                            data: data,
                            success: function(response) {
                                Swal.fire({
                                    text: "Data berhasil disimpan!",
                                    icon: "success",
                                    buttonsStyling: false,
                                    confirmButtonText: "Lanjutkan",
                                    customClass: {
                                        confirmButton: "btn btn-primary"
                                    }
                                }).then(function (result) {
                                    if (result.isConfirmed) {
                                        let redirectUrl = "{{ route('admin.stok-masuk.daftar-penerimaan-barang.index') }}";
                                        if (response && response.redirect_url) {
                                            redirectUrl = response.redirect_url;
                                        }
                                        window.location.href = redirectUrl;
                                    }
                                });
                            },
                            error: function(xhr) {
                                if (xhr.status === 422) {
                                    // Handle validation errors
                                    var errors = xhr.responseJSON.errors;
                                    let errorMessages = '';
                                    $.each(errors, function(key, value) {
                                        let field = $('[name="' + key + '"]');
                                        // Handle array validation keys like 'items.0.item_id'
                                        if(key.includes('.')) {
                                            const parts = key.split('.');
                                            field = $('[name="items['+parts[1]+']['+parts[2]+']"]');
                                        }
                                        field.addClass('is-invalid');
                                        field.after('<div class="invalid-feedback">' + value[0] + '</div>');
                                        errorMessages += `<li>${value[0]}</li>`;
                                    });
                                    toastr.error('<ul>' + errorMessages + '</ul>', 'Validasi Gagal');
                                } else {
                                    // Handle other server errors
                                    toastr.error('Terjadi kesalahan pada server. Silakan coba lagi.', 'Error');
                                }
                            }
                        });
                    }
                });
            });
        });
    </script>
@endpush
