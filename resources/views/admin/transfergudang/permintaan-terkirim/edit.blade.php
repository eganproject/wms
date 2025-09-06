@extends('layouts.app')

@push('styles')
    <style>
        .select2-container .select2-selection--single {
            height: 2.65rem !important;
        }
        .available-stock {
            font-size: 0.9rem;
            color: #5e6278;
            margin-top: 5px;
        }
    </style>
@endpush

@push('toolbar')
    @include('layouts.partials._toolbar', [
        'title' => 'Edit Permintaan Transfer',
        'breadcrumbs' => ['Admin', 'Transfer Gudang', 'Edit Permintaan Transfer'],
    ])
@endpush

@section('content')
    <div class="content flex-row-fluid" id="kt_content">
        <div class="card">
            <div class="card-body">
                <form id="transfer-request-form" action="{{ route('admin.transfergudang.permintaan-terkirim.update', $transferRequest->id) }}"
                    method="POST">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-md-4 mb-5">
                            <label class="form-label">Kode Permintaan</label>
                            <input type="text" name="code"
                                class="form-control form-control-solid  @error('code') is-invalid @enderror"
                                value="{{ $transferRequest->code }}" readonly />
                        </div>
                        <div class="col-md-4 mb-5">
                            <label class="form-label required">Tanggal Permintaan</label>
                            <input type="date" name="date" class="form-control form-control-solid @error('date') is-invalid @enderror"
                                value="{{ old('date', (new DateTime($transferRequest->date))->format('Y-m-d')) }}">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-5">
                            <label class="form-label required">Gudang Asal</label>
                            <select name="from_warehouse_id" id="from_warehouse_id"
                                class="form-select form-select-solid @error('from_warehouse_id') is-invalid @enderror" data-control="select2"
                                data-placeholder="Pilih gudang asal">
                                <option></option>
                                @foreach ($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}"
                                        {{ old('from_warehouse_id', $transferRequest->from_warehouse_id) == $warehouse->id ? 'selected' : '' }}>
                                        {{ $warehouse->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-5">
                            <label class="form-label required">Gudang Tujuan</label>
                            @if (auth()->user()->warehouse_id)
                                <input type="hidden" name="to_warehouse_id" value="{{ auth()->user()->warehouse_id }}">
                                <input type="text" class="form-control form-control-solid" value="{{ auth()->user()->warehouse->name }}" readonly>
                            @else
                                <select name="to_warehouse_id" id="to_warehouse_id"
                                    class="form-select form-select-solid @error('to_warehouse_id') is-invalid @enderror" data-control="select2"
                                    data-placeholder="Pilih gudang tujuan">
                                    <option></option>
                                    @foreach ($warehouses as $warehouse)
                                        <option value="{{ $warehouse->id }}"
                                            {{ old('to_warehouse_id', $transferRequest->to_warehouse_id) == $warehouse->id ? 'selected' : '' }}>
                                            {{ $warehouse->name }}</option>
                                    @endforeach
                                </select>
                            @endif
                        </div>
                    </div>

                    <div class="mb-5">
                        <label class="form-label">Deskripsi Permintaan</label>
                        <textarea name="description" class="form-control form-control-solid @error('description') is-invalid @enderror" rows="3">{{ old('description', $transferRequest->description) }}</textarea>
                    </div>

                    <h4 class="mt-10">Daftar Item</h4>
                    <div class="table-responsive">
                        <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4" id="items-table">
                            <thead>
                                <tr class="fw-bolder text-muted">
                                    <th class="min-w-300px">Item</th>
                                    <th class="min-w-150px">Jumlah</th>
                                    <th class="min-w-150px">Koli</th>
                                    <th class="min-w-200px">Deskripsi Item</th>
                                    <th class="min-w-50px text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Item rows will be added here -->
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="5">
                                        <button type="button" class="btn btn-light-primary" id="add-item-btn">+ Tambah
                                            Item</button>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="mt-10">
                        <button type="submit" class="btn btn-primary">Update</button>
                        <a href="{{ route('admin.transfergudang.permintaan-terkirim.index') }}"
                            class="btn btn-light">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Template for new item row -->
    <template id="item-row-template">
        <tr data-index="__INDEX__">
            <td>
                <select name="items[__INDEX__][item_id]" class="form-select form-select-solid item-select" data-placeholder="Pilih item"
                    required>
                    <option></option>
                    {{-- Options will be populated by JS --}}
                </select>
            </td>
            <td>
                <input type="number" name="items[__INDEX__][quantity]" class="form-control form-control-solid quantity-input" min="0.01"
                    step="0.01" value="1" required>
                <div class="available-stock"></div>
            </td>
            <td>
                <input type="number" name="items[__INDEX__][koli]" class="form-control form-control-solid koli-input" min="0"
                    step="any" value="0">
            </td>
            <td>
                <input type="text" name="items[__INDEX__][description]" class="form-control form-control-solid">
            </td>
            <td class="text-end">
                <button type="button" class="btn btn-icon btn-sm btn-danger remove-item-btn"><i
                        class="bi bi-trash"></i></button>
            </td>
        </tr>
    </template>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            let itemIndex = 0;
            let availableItems = [];

            function initializeSelect2(element) {
                $(element).select2({
                    width: '100%',
                    placeholder: "Pilih item",
                });
            }

            function updateItemSelectOptions(selectElement, savedItemId = null) {
                const currentVal = $(selectElement).val() || savedItemId;
                $(selectElement).empty().append($('<option></option>'));
                
                let savedItemInAvailableList = false;
                availableItems.forEach(function(inventoryItem) {
                    if(inventoryItem.item_id == currentVal) savedItemInAvailableList = true;
                    let option = new Option(
                        `${inventoryItem.item.nama_barang} (SKU: ${inventoryItem.item.sku})`,
                        inventoryItem.item_id,
                        false,
                        false
                    );
                    $(option).attr('data-quantity', inventoryItem.quantity);
                    $(option).attr('data-koli', inventoryItem.item.koli_per_uom || 1);
                    $(selectElement).append(option);
                });

                if (currentVal && !savedItemInAvailableList) {
                    let existingItemData = @json($transferRequest->items->keyBy('item_id'));
                    let itemDetails = existingItemData[currentVal];
                    if(itemDetails && itemDetails.item) {
                        let option = new Option(
                            `${itemDetails.item.name} (SKU: ${itemDetails.item.sku}) - Stok Habis`,
                            itemDetails.item_id,
                            true,
                            true
                        );
                        $(option).attr('data-quantity', 0).prop('disabled', true);
                        $(selectElement).append(option);
                    }
                }

                $(selectElement).val(currentVal).trigger('change');
            }

            function addNewRow(itemData = null) {
                const template = document.getElementById('item-row-template').innerHTML;
                const newRowHtml = template.replace(/__INDEX__/g, itemIndex);
                const newRow = $(newRowHtml);
                $('#items-table tbody').append(newRow);

                const select = newRow.find('.item-select');
                updateItemSelectOptions(select, itemData ? itemData.item_id : null);
                initializeSelect2(select);

                if (itemData) {
                    select.val(itemData.item_id).trigger('change');
                    newRow.find('.quantity-input').val(itemData.quantity);
                    newRow.find('.koli-input').val(itemData.koli);
                    newRow.find('input[name*="description"]').val(itemData.description);
                }

                itemIndex++;
            }

            function loadInitialData() {
                const warehouseId = $('#from_warehouse_id').val();
                const existingItems = @json(old('items', $transferRequest->items));

                if (!warehouseId) {
                    if (existingItems && existingItems.length > 0) {
                        existingItems.forEach(function(item) { addNewRow(item); });
                    }
                    return;
                }

                $.ajax({
                    url: `{{ url('admin/transfer-gudang/get-items-by-warehouse') }}/${warehouseId}`,
                    type: 'GET',
                    success: function(items) {
                        availableItems = items;
                        if (existingItems && existingItems.length > 0) {
                            existingItems.forEach(function(item) { addNewRow(item); });
                        }
                    },
                    error: function(xhr) {
                        console.error('Error fetching items:', xhr);
                        toastr.error('Gagal mengambil data item dari gudang.', 'Error');
                        if (existingItems && existingItems.length > 0) {
                            existingItems.forEach(function(item) { addNewRow(item); });
                        }
                    }
                });
            }

            $('#add-item-btn').on('click', function() {
                if (!$('#from_warehouse_id').val()) {
                    Swal.fire('Peringatan', 'Silakan pilih Gudang Asal terlebih dahulu.','warning');
                    return;
                }
                addNewRow();
            });

            $('#items-table').on('click', '.remove-item-btn', function() {
                $(this).closest('tr').remove();
            });

            function checkWarehouseSelection() {
                const fromWarehouse = $('#from_warehouse_id').val();
                const toWarehouse = $('[name="to_warehouse_id"]').val();

                if (fromWarehouse && toWarehouse && fromWarehouse === toWarehouse) {
                    Swal.fire(
                        'Gudang Tidak Valid',
                        'Gudang Asal dan Gudang Tujuan tidak boleh sama.',
                        'warning'
                    );
                    // Clear the selection of the element that was just changed
                    // This is a bit tricky to determine, so we can just clear one, e.g., to_warehouse
                    if ($('#to_warehouse_id').is('select')) {
                        $('#to_warehouse_id').val(null).trigger('change');
                    }
                }
            }

            $('#from_warehouse_id, #to_warehouse_id').on('change', checkWarehouseSelection);

            $('#from_warehouse_id').on('change', function() {
                const warehouseId = $(this).val();
                $('#items-table tbody').empty();
                itemIndex = 0;
                availableItems = [];

                if (!warehouseId) return;

                $.ajax({
                    url: `{{ url('admin/transfer-gudang/get-items-by-warehouse') }}/${warehouseId}`,
                    type: 'GET',
                    success: function(items) {
                        availableItems = items;
                        // Only add new row if there are no existing items loaded from old() or $transferRequest->items
                        if (!(@json(old('items')) || @json($transferRequest->items)).length) {
                            addNewRow();
                        }
                    },
                    error: function(xhr) {
                        console.error('Error fetching items:', xhr);
                        toastr.error('Gagal mengambil data item dari gudang.', 'Error');
                    }
                });
            });

            $('#items-table').on('focus', '.item-select', function() {
                $(this).data('previous-value', $(this).val());
            });

            $('#items-table').on('change', '.item-select', function() {
                const currentSelect = this;
                const selectedItemId = $(currentSelect).val();

                if (selectedItemId) {
                    let isDuplicate = false;
                    $('.item-select').not(currentSelect).each(function() {
                        if ($(this).val() === selectedItemId) {
                            isDuplicate = true;
                            return false;
                        }
                    });

                    if (isDuplicate) {
                        Swal.fire('Item Sudah Dipilih','Item ini sudah ada di daftar. Silakan pilih item lain.','error');
                        const previousValue = $(currentSelect).data('previous-value');
                        $(currentSelect).val(previousValue).trigger('change.select2');
                        return;
                    }
                }

                $(currentSelect).data('previous-value', selectedItemId);

                const selectedOption = $(this).find('option:selected');
                const quantity = selectedOption.data('quantity');
                const stockInfo = $(this).closest('tr').find('.available-stock');

                if (quantity !== undefined) {
                    stockInfo.text(`Stok: ${quantity}`);
                } else {
                    stockInfo.text('');
                }
                calculateItemValues($(this).closest('tr'), 'quantity');
            });

            function validateQuantity(inputElement) {
                const row = $(inputElement).closest('tr');
                const selectedOption = row.find('.item-select option:selected');
                const availableQuantity = parseFloat(selectedOption.data('quantity'));
                const enteredQuantity = parseFloat($(inputElement).val());

                if (isNaN(availableQuantity)) return;

                if (enteredQuantity > availableQuantity) {
                    Swal.fire('Kuantitas Melebihi Stok',`Stok yang tersedia hanya ${availableQuantity}. Kuantitas Anda telah disesuaikan.`,'error');
                    $(inputElement).val(availableQuantity);
                    calculateItemValues(row, 'quantity');
                }
            }

            $('#items-table').on('input', '.quantity-input', function() {
                validateQuantity(this);
                calculateItemValues($(this).closest('tr'), 'quantity');
            });

            $('#items-table').on('input', '.koli-input', function() {
                calculateItemValues($(this).closest('tr'), 'koli');
                const row = $(this).closest('tr');
                const quantityInput = row.find('.quantity-input');
                validateQuantity(quantityInput);
            });

            function calculateItemValues(row, changedField) {
                let itemId = row.find('.item-select').val();
                let quantityInput = row.find('.quantity-input');
                let koliInput = row.find('.koli-input');
                let data = { _token: '{{ csrf_token() }}', item_id: itemId };

                if (!itemId) return;

                if (changedField === 'quantity') {
                    data.quantity = parseFloat(quantityInput.val()) || 0;
                } else if (changedField === 'koli') {
                    data.koli = parseFloat(koliInput.val()) || 0;
                }

                $.ajax({
                    url: '{{ route('admin.transfergudang.calculate-item-values') }}',
                    type: 'POST',
                    data: data,
                    success: function(response) {
                        if (changedField === 'quantity') {
                            koliInput.val(response.koli.toFixed(2));
                        } else if (changedField === 'koli') {
                            quantityInput.val(response.quantity.toFixed(2));
                        }
                    },
                    error: function(xhr) { console.error('Error calculating item values:', xhr); }
                });
            }

            $(`[data-control='select2']`).select2();

            loadInitialData();

            $('#transfer-request-form').on('submit', function(e) {
                e.preventDefault();
                var form = $(this);

                Swal.fire({
                    text: "Apakah Anda yakin ingin menyimpan perubahan ini?",
                    icon: "question",
                    showCancelButton: true,
                    buttonsStyling: false,
                    confirmButtonText: "Ya, Simpan!",
                    cancelButtonText: "Tidak, Batalkan",
                    customClass: { confirmButton: "btn fw-bold btn-primary", cancelButton: "btn fw-bold btn-active-light-primary" }
                }).then(function(result) {
                    if (result.value) {
                        let allQuantitiesValid = true;
                        $('.quantity-input').each(function() {
                            const row = $(this).closest('tr');
                            const selectedOption = row.find('.item-select option:selected');
                            const availableQuantity = parseFloat(selectedOption.data('quantity'));
                            const enteredQuantity = parseFloat($(this).val());
                            if (!isNaN(availableQuantity) && enteredQuantity > availableQuantity) {
                                allQuantitiesValid = false;
                            }
                        });

                        if (!allQuantitiesValid) {
                            Swal.fire('Error', 'Satu atau lebih item memiliki kuantitas melebihi stok. Silakan perbaiki.', 'error');
                            return;
                        }

                        $.ajax({
                            url: form.attr('action'),
                            type: 'POST',
                            data: form.serialize(),
                            success: function(response) {
                                Swal.fire({
                                    text: "Data berhasil diubah!",
                                    icon: "success",
                                    buttonsStyling: false,
                                    confirmButtonText: "Lanjutkan",
                                    customClass: { confirmButton: "btn btn-primary" }
                                }).then(function(result) {
                                    if (result.isConfirmed) {
                                        window.location.href = "{{ route('admin.transfergudang.permintaan-terkirim.index') }}";
                                    }
                                });
                            },
                            error: function(xhr) {
                                $('.is-invalid').removeClass('is-invalid');
                                $('.invalid-feedback').remove();
                                if (xhr.status === 422) {
                                    var errors = xhr.responseJSON.errors;
                                    $.each(errors, function(key, value) {
                                        let field = $('[name="' + key + '"]');
                                        if (key.includes('.')) {
                                            const parts = key.split('.');
                                            field = $('[name="items[' + parts[1] + '][' + parts[2] + ']"]');
                                        }
                                        field.addClass('is-invalid').after('<div class="invalid-feedback">' + value[0] + '</div>');
                                    });
                                    toastr.error('Silakan perbaiki error validasi yang ada.','Validasi Gagal');
                                } else {
                                    toastr.error('Terjadi kesalahan pada server.', 'Error');
                                }
                            }
                        });
                    }
                });
            });
        });
    </script>
@endpush
