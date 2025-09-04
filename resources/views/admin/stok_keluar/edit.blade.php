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
                            <label class="form-label required">Kode Pengeluaran</label>
                            <input type="text" name="code" id="code" class="form-control" value="{{ $pengeluaranBarang->code }}" readonly>
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
                                    <th class="min-w-150px">Jumlah Koli</th>
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
                                        <input type="number" name="items[{{$index}}][quantity]" class="form-control quantity-input" min="1" value="{{ $itemOut['quantity'] ?? 1 }}" step="any">
                                    </td>
                                    <td>
                                        <input type="number" name="items[{{$index}}][koli]" class="form-control koli-input" min="0" value="{{ $itemOut['koli'] ?? 0 }}" step="any">
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
                <input type="number" name="items[__INDEX__][quantity]" class="form-control quantity-input" min="1" value="1" step="any">
            </td>
            <td>
                <input type="number" name="items[__INDEX__][koli]" class="form-control koli-input" min="0" value="0" step="any">
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
                    $(option).attr('data-item-koli', inventoryItem.item.koli); // Add this line
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

                // Trigger recalculation for quantity and koli when item changes
                const row = $(this).closest('tr');
                const quantityInput = row.find('.quantity-input');
                const koliInput = row.find('.koli-input');
                
                // If quantity is already set, update koli based on quantity
                if (parseFloat(quantityInput.val()) > 0) {
                    quantityInput.trigger('input');
                } else if (parseFloat(koliInput.val()) > 0) { // Otherwise, if koli is set, update quantity
                    koliInput.trigger('input');
                } else { // Default to 1 quantity if nothing is set
                    quantityInput.val(1).trigger('input');
                }

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
                const row = $(this).closest('tr');
                const selectedOption = row.find('.item-select option:selected');
                const itemKoli = parseFloat(selectedOption.data('item-koli'));
                let quantity = parseFloat($(this).val());
                const koliInput = row.find('.koli-input');
                const availableQuantity = parseFloat(selectedOption.data('quantity'));

                // Validate quantity against available stock
                if (!isNaN(availableQuantity) && quantity > availableQuantity) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Stok Tidak Cukup',
                        text: `Stok yang tersedia hanya ${availableQuantity}. Kuantitas telah disesuaikan.`
                    });
                    $(this).val(availableQuantity);
                    quantity = availableQuantity;
                }

                // Update koli value whenever quantity changes
                if (!isNaN(quantity) && !isNaN(itemKoli) && itemKoli > 0) {
                    const koliValue = parseFloat(quantity) / parseFloat(itemKoli);
                    koliInput.val(parseFloat(koliValue).toFixed(2));
                } else {
                    koliInput.val(0);
                }
            });

            $('#items-table').on('input', '.koli-input', function() {
                const row = $(this).closest('tr');
                const selectedOption = row.find('.item-select option:selected');
                const itemKoli = parseFloat(selectedOption.data('item-koli'));
                const koli = parseFloat($(this).val());
                const quantityInput = row.find('.quantity-input');
                const availableQuantity = parseFloat(selectedOption.data('quantity'));

                if (!isNaN(koli) && !isNaN(itemKoli)) {
                    const newQuantity = koli * itemKoli;
                    
                    // Validate if the new quantity would exceed available stock
                    if (!isNaN(availableQuantity) && newQuantity > availableQuantity) {
                        const maxKoli = availableQuantity / itemKoli;
                        Swal.fire({
                            icon: 'warning',
                            title: 'Stok Tidak Cukup',
                            text: `Stok yang tersedia hanya ${availableQuantity} (${maxKoli.toFixed(2)} koli). Jumlah koli telah disesuaikan.`
                        });
                        $(this).val(maxKoli.toFixed(2));
                        quantityInput.val(maxKoli * itemKoli);
                    } else {
                        quantityInput.val(newQuantity);
                    }
                } else {
                    quantityInput.val(0);
                }
            });