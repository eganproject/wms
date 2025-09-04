@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        .select2-container .select2-selection--single {
            height: 2.65rem !important;
        }
        .is-invalid .select2-container .select2-selection--single {
            border-color: #f1416c !important;
        }
    </style>
@endpush

@push('toolbar')
    @include('layouts.partials._toolbar', [
        'title' => 'Buat Pengeluaran Barang',
        'breadcrumbs' => ['Admin', 'Stok Keluar', 'Buat Pengeluaran Barang'],
    ])
@endpush

@section('content')
    <div class="content flex-row-fluid" id="kt_content">
        <div class="card">
            <div class="card-body">
                <form id="stock-out-form" action="{{ route('admin.stok-keluar.pengeluaran-barang.store') }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 mb-5">
                            <label class="form-label required">Tanggal Pengeluaran</label>
                            <input type="text" name="stock_out_date" id="stock_out_date" class="form-control @error('stock_out_date') is-invalid @enderror" value="{{ old('stock_out_date', date('Y-m-d')) }}">
                            @error('stock_out_date')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-5">
                            <label class="form-label required">Gudang</label>
                            <select name="warehouse_id" id="warehouse_id" class="form-select @error('warehouse_id') is-invalid @enderror" data-control="select2" data-placeholder="Pilih gudang">
                                <option></option>
                                @foreach ($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}" {{ old('warehouse_id') == $warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }}</option>
                                @endforeach
                            </select>
                             @error('warehouse_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-5">
                        <label class="form-label">Catatan</label>
                        <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="3">{{ old('notes') }}</textarea>
                         @error('notes')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <h4 class="mt-10">Daftar Item</h4>
                     @if ($errors->has('items') || $errors->has('items.*'))
                        <div class="alert alert-danger">
                            Terdapat kesalahan pada daftar item. Silakan periksa kembali kuantitas dan pastikan stok mencukupi.
                        </div>
                    @endif
                    <div class="table-responsive">
                        <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4" id="items-table">
                            <thead>
                                <tr class="fw-bolder text-muted">
                                    <th class="min-w-350px">Item</th>
                                    <th class="min-w-150px">Stok Tersedia</th>
                                    <th class="min-w-150px">Quantity</th>
                                    <th class="min-w-50px text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Dynamic rows will be added here -->
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="4">
                                        <button type="button" class="btn btn-light-primary" id="add-item-btn">+ Tambah Item</button>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="mt-10">
                        <button type="submit" class="btn btn-primary">Simpan</button>
                        <a href="{{ route('admin.stok-keluar.pengeluaran-barang.index') }}" class="btn btn-light">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <template id="item-row-template">
        <tr data-index="__INDEX__">
            <td>
                <select name="items[__INDEX__][item_id]" class="form-select item-select" data-placeholder="Pilih item"></select>
                <div class="invalid-feedback-custom text-danger mt-2"></div>
            </td>
            <td><span class="available-stock text-muted">-</span></td>
            <td>
                <input type="number" name="items[__INDEX__][quantity]" class="form-control quantity-input" min="1" value="1">
            </td>
            <td class="text-end">
                <button type="button" class="btn btn-icon btn-sm btn-danger remove-item-btn"><i class="bi bi-trash"></i></button>
            </td>
        </tr>
    </template>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        $(document).ready(function() {
            window.inventoryData = @json($inventory);
            let itemIndex = 0;

            $("#stock_out_date").flatpickr({
                dateFormat: "Y-m-d",
                defaultDate: "{{ old('stock_out_date', date('Y-m-d')) }}"
            });

            function initializeSelect2(element) {
                $(element).select2({ width: '100%', placeholder: "Pilih item" });
            }

            function updateItemSelectOptions(selectElement, warehouseId) {
                const selectedItemId = $(selectElement).val();
                $(selectElement).empty().append($('<option value=""></option>'));
                const itemsInWarehouse = window.inventoryData[warehouseId] || [];
                itemsInWarehouse.forEach(function(inventoryItem) {
                    let optionText = `${inventoryItem.item.name} (SKU: ${inventoryItem.item.sku})`;
                    let option = new Option(optionText, inventoryItem.item_id, false, false);
                    $(option).attr('data-quantity', inventoryItem.quantity);
                    $(selectElement).append(option);
                });
                $(selectElement).val(selectedItemId).trigger('change');
            }

            function addNewRow() {
                const warehouseId = $('#warehouse_id').val();
                if (!warehouseId) return;
                const template = document.getElementById('item-row-template').innerHTML.replace(/__INDEX__/g, itemIndex);
                const newRow = $(template);
                $('#items-table tbody').append(newRow);
                const select = newRow.find('.item-select');
                updateItemSelectOptions(select, warehouseId);
                initializeSelect2(select);
                itemIndex++;
            }

            $('#add-item-btn').on('click', function() {
                if (!$('#warehouse_id').val()) {
                    Swal.fire('Peringatan', 'Silakan pilih Gudang terlebih dahulu.', 'warning');
                    return;
                }
                addNewRow();
            });

            $('#items-table').on('click', '.remove-item-btn', function() {
                $(this).closest('tr').remove();
            });

            $('#warehouse_id').on('change', function() {
                const warehouseId = $(this).val();
                $('#items-table tbody').empty();
                itemIndex = 0;
                if (warehouseId) {
                    addNewRow();
                }
            });

            $('#items-table').on('change', '.item-select', function() {
                const currentSelect = this;
                const selectedItemId = $(currentSelect).val();

                $(currentSelect).closest('td').removeClass('is-invalid');
                $(currentSelect).closest('td').find('.invalid-feedback-custom').text('');

                if (selectedItemId) {
                    let isDuplicate = false;
                    $('.item-select').not(currentSelect).each(function() {
                        if ($(this).val() === selectedItemId) {
                            isDuplicate = true;
                            return false;
                        }
                    });

                    if (isDuplicate) {
                        Swal.fire('Peringatan', 'Item ini sudah dipilih di baris lain.', 'warning');
                        $(currentSelect).val('').trigger('change');
                        return;
                    }
                }

                const selectedOption = $(this).find('option:selected');
                const quantity = selectedOption.data('quantity');
                const stockInfo = $(this).closest('tr').find('.available-stock');
                stockInfo.text(quantity !== undefined ? quantity : '-');
                validateQuantity($(this).closest('tr').find('.quantity-input'));
            });

            function validateQuantity(inputElement) {
                const row = $(inputElement).closest('tr');
                const selectedOption = row.find('.item-select option:selected');
                const availableQuantity = parseFloat(selectedOption.data('quantity'));
                const enteredQuantity = parseFloat($(inputElement).val());

                if (isNaN(availableQuantity)) return;

                if (enteredQuantity > availableQuantity) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Stok Tidak Cukup',
                        text: `Stok yang tersedia hanya ${availableQuantity}. Kuantitas telah disesuaikan.`
                    });
                    $(inputElement).val(availableQuantity);
                }
            }

            $('#items-table').on('input', '.quantity-input', function() {
                validateQuantity(this);
            });

            $(`[data-control='select2']`).select2();

            const oldItems = @json(old('items'));
            if (oldItems) {
                let warehouseId = $('#warehouse_id').val();
                if(warehouseId){
                    oldItems.forEach(function(item, index) {
                        addNewRow();
                        let newRow = $('#items-table tbody tr').last();
                        let select = newRow.find('.item-select');
                        select.val(item.item_id).trigger('change');
                        newRow.find('.quantity-input').val(item.quantity);
                    });
                }
            } else {
                if ($('#warehouse_id').val()) {
                    $('#warehouse_id').trigger('change');
                }
            }

            $('#stock-out-form').on('submit', function(e) {
                e.preventDefault();
                let isValid = true;
                const form = this;

                if ($('#items-table tbody tr').length === 0) {
                    Swal.fire('Peringatan', 'Harap tambahkan setidaknya satu item.', 'warning');
                    return;
                }

                $('select.item-select').each(function() {
                    const td = $(this).closest('td');
                    td.removeClass('is-invalid');
                    td.find('.invalid-feedback-custom').text('');
                    
                    if (!$(this).val()) {

                        isValid = false;
                        td.addClass('is-invalid');
                        td.find('.invalid-feedback-custom').text('Item harus dipilih.');
                    }
                });

                $('.quantity-input').each(function() {
                    const row = $(this).closest('tr');
                    const selectedOption = row.find('select.item-select option:selected');
                    const availableQuantity = parseFloat(selectedOption.data('quantity'));
                    const enteredQuantity = parseFloat($(this).val());

                    if (isNaN(enteredQuantity) || enteredQuantity <= 0) {
                        isValid = false;
                        $(this).addClass('is-invalid');
                    } else {
                        $(this).removeClass('is-invalid');
                    }

                    if (!isNaN(availableQuantity) && enteredQuantity > availableQuantity) {
                        isValid = false;
                        $(this).addClass('is-invalid');
                    }
                });

                if (isValid) {
                    Swal.fire({
                        text: "Apakah Anda yakin ingin menyimpan data ini?",
                        icon: "question",
                        showCancelButton: true,
                        buttonsStyling: false,
                        confirmButtonText: "Ya, simpan!",
                        cancelButtonText: "Tidak, batalkan",
                        customClass: {
                            confirmButton: "btn fw-bold btn-primary",
                            cancelButton: "btn fw-bold btn-active-light-primary"
                        }
                    }).then(function (result) {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                } else {
                    Swal.fire('Peringatan', 'Harap perbaiki semua error sebelum menyimpan. Pastikan semua item dan kuantitas valid.', 'warning');
                }
            });
        });
    </script>
@endpush