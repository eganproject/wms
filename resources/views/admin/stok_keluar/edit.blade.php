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
        'title' => 'Edit Pengeluaran Barang',
        'breadcrumbs' => ['Admin', 'Stok Keluar', 'Edit Pengeluaran Barang'],
    ])
@endpush

@section('content')
    <div class="content flex-row-fluid" id="kt_content">
        <div class="card">
            <div class="card-body">
                <form id="stock-out-form" action="{{ route('admin.stok-keluar.pengeluaran-barang.update', $pengeluaranBarang->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-md-6 mb-5">
                            <label class="form-label required">Tanggal Pengeluaran</label>
                            <input type="text" name="stock_out_date" id="stock_out_date" class="form-control @error('stock_out_date') is-invalid @enderror" value="{{ old('stock_out_date', $pengeluaranBarang->stock_out_date->format('Y-m-d')) }}">
                            @error('stock_out_date')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-5">
                            <label class="form-label required">Gudang</label>
                            <select name="warehouse_id" id="warehouse_id" class="form-select @error('warehouse_id') is-invalid @enderror" data-control="select2" data-placeholder="Pilih gudang">
                                <option></option>
                                @foreach ($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}" {{ old('warehouse_id', $pengeluaranBarang->warehouse_id) == $warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }}</option>
                                @endforeach
                            </select>
                             @error('warehouse_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-5">
                        <label class="form-label">Catatan</label>
                        <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="3">{{ old('notes', $pengeluaranBarang->notes) }}</textarea>
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
                                @foreach(old('items', $pengeluaranBarang->items->toArray()) as $index => $itemOut)
                                <tr data-index="{{$index}}">
                                    <td>
                                        <select name="items[{{$index}}][item_id]" class="form-select item-select" data-placeholder="Pilih item">
                                            @if(isset($itemOut['item_id']))
                                                <option value="{{ $itemOut['item_id'] }}" selected>{{ data_get($itemOut, 'item.name', '') }} (SKU: {{ data_get($itemOut, 'item.sku', '') }})</option>
                                            @endif
                                        </select>
                                        <div class="invalid-feedback-custom text-danger mt-2"></div>
                                    </td>
                                    <td><span class="available-stock text-muted">-</span></td>
                                    <td>
                                        <input type="number" name="items[{{$index}}][quantity]" class="form-control quantity-input" min="1" value="{{ $itemOut['quantity'] ?? 1 }}">
                                    </td>
                                    <td class="text-end">
                                        <button type="button" class="btn btn-icon btn-sm btn-danger remove-item-btn"><i class="bi bi-trash"></i></button>
                                    </td>
                                </tr>
                                @endforeach
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
                        <button type="submit" class="btn btn-primary">Update</button>
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
            window.stockOutItems = @json($pengeluaranBarang->items->keyBy('item_id'));

            let itemIndex = {{ count(old('items', $pengeluaranBarang->items->toArray())) }};

            $("#stock_out_date").flatpickr({
                dateFormat: "Y-m-d",
                defaultDate: "{{ old('stock_out_date', $pengeluaranBarang->stock_out_date->format('Y-m-d')) }}"
            });

            function initializeSelect2(element) {
                $(element).select2({ width: '100%', placeholder: "Pilih item" });
            }

            function updateItemSelectOptions(selectElement, warehouseId, selectedId = null) {
                const currentVal = $(selectElement).val() || selectedId;
                $(selectElement).empty().append($('<option value=""></option>'));
                
                const itemsInWarehouse = window.inventoryData[warehouseId] || [];

                itemsInWarehouse.forEach(function(inventoryItem) {
                    let optionText = `${inventoryItem.item.name} (SKU: ${inventoryItem.item.sku})`;
                    let option = new Option(optionText, inventoryItem.item_id, false, false);
                    
                    let originalItem = window.stockOutItems[inventoryItem.item_id];
                    let originalQuantity = originalItem ? parseFloat(originalItem.quantity) : 0;
                    let stockOnHand = parseFloat(inventoryItem.quantity);

                    let availableStock = stockOnHand + originalQuantity;

                    $(option).attr('data-quantity', availableStock);
                    $(selectElement).append(option);
                });

                $(selectElement).val(currentVal).trigger('change');
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

            // Initial setup
            $(`[data-control='select2']`).select2();
            const initialWarehouseId = $('#warehouse_id').val();
            if (initialWarehouseId) {
                $('.item-select').each(function() {
                    const selectedId = $(this).val();
                    updateItemSelectOptions(this, initialWarehouseId, selectedId);
                    initializeSelect2(this);
                    $(this).trigger('change');
                });
            }

            $('#stock-out-form').on('submit', function(e) {
                e.preventDefault();
                let isValid = true;
                const form = this;

                $('.item-select').each(function() {
                    const td = $(this).closest('td');
                    td.removeClass('is-invalid');
                    td.find('.invalid-feedback-custom').text('');

                    if (!$(this).hasClass('select2-hidden-accessible')) return;

                    const selectedData = $(this).select2('data');
                    if (selectedData.length === 0 || !selectedData[0].id) {
                        isValid = false;
                        td.addClass('is-invalid');
                        td.find('.invalid-feedback-custom').text('Item harus dipilih.');
                    }
                });

                if ($('#items-table tbody tr').length === 0) {
                    isValid = false;
                } 

                if (isValid) {
                    Swal.fire({
                        text: "Apakah Anda yakin ingin menyimpan perubahan ini?",
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
                    Swal.fire('Peringatan', 'Harap perbaiki semua error sebelum menyimpan.', 'warning');
                }
            });
        });
    </script>
@endpush