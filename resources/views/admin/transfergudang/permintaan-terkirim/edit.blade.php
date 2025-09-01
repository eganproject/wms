@extends('layouts.app')

@push('styles')
    <style>
        .select2-container .select2-selection--single {
            height: 2.65rem !important;
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
        <form id="transfer-request-form" action="{{ route('admin.transfergudang.permintaan-terkirim.update', $transferRequest->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="row">
                <div class="col-md-4 mb-5">
                    <label class="form-label">Kode Permintaan</label>
                    <input type="text" name="code" class="form-control form-control-solid @error('code') is-invalid @enderror" value="{{ $transferRequest->code }}" readonly/>
                    @error('code')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4 mb-5">
                    <label class="form-label required">Tanggal Permintaan</label>
                    <input type="date" name="date" class="form-control @error('date') is-invalid @enderror" value="{{ old('date', (new DateTime($transferRequest->date))->format('Y-m-d')) }}">
                    @error('date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-5">
                    <label class="form-label required">Gudang Asal</label>
                    <select name="from_warehouse_id" class="form-select @error('from_warehouse_id') is-invalid @enderror" data-control="select2" data-placeholder="Pilih gudang asal">
                        <option></option>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" {{ old('from_warehouse_id', $transferRequest->from_warehouse_id) == $warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                    @error('from_warehouse_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6 mb-5">
                    <label class="form-label required">Gudang Tujuan</label>
                    <select name="to_warehouse_id" class="form-select @error('to_warehouse_id') is-invalid @enderror" data-control="select2" data-placeholder="Pilih gudang tujuan">
                        <option></option>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" {{ old('to_warehouse_id', $transferRequest->to_warehouse_id) == $warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                    @error('to_warehouse_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="mb-5">
                <label class="form-label">Deskripsi Permintaan</label>
                <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description', $transferRequest->description) }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <h4 class="mt-10">Daftar Item</h4>
            <div class="table-responsive">
                <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4" id="items-table">
                    <thead>
                        <tr class="fw-bolder text-muted">
                            <th class="min-w-250px">Item</th>
                            <th class="min-w-125px">Jumlah</th>
                            <th class="min-w-125px">Koli</th>
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
                                <button type="button" class="btn btn-light-primary" id="add-item-btn">+ Tambah Item</button>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            @error('items')
                <div class="text-danger mt-2">{{ $message }}</div>
            @enderror

            <div class="mt-10">
                <button type="submit" class="btn btn-primary">Update</button>
                <a href="{{ route('admin.transfergudang.permintaan-terkirim.index') }}" class="btn btn-light">Batal</a>
            </div>
        </form>
    </div>
</div>
</div>

<!-- Template for new item row -->
<template id="item-row-template">
    <tr data-index="__INDEX__">
        <td>
            <select name="items[__INDEX__][item_id]" class="form-select item-select" data-placeholder="Pilih item" required>
                <option></option>
                @foreach($items as $item)
                    <option value="{{ $item->id }}" data-koli="{{ $item->koli_per_uom ?? 1 }}">{{ $item->name }} (SKU: {{ $item->sku }})</option>
                @endforeach
            </select>
        </td>
        <td>
            <input type="number" name="items[__INDEX__][quantity]" class="form-control quantity-input" min="0.01" step="0.01" value="1" required>
        </td>
        <td>
            <input type="number" name="items[__INDEX__][koli]" class="form-control koli-input" min="0" step="any" value="0">
        </td>
        <td>
            <input type="text" name="items[__INDEX__][description]" class="form-control">
        </td>
        <td class="text-end">
            <button type="button" class="btn btn-icon btn-sm btn-danger remove-item-btn"><i class="bi bi-trash"></i></button>
        </td>
    </tr>
</template>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        let itemIndex = 0;
        var itemsData = @json($items->map(function($item) {
            return [
                'id' => $item->id,
                'text' => $item->name . ' (SKU: ' . $item->sku . ')',
                'koli_per_uom' => $item->koli_per_uom ?? 1
            ];
        }));

        function initializeSelect2(element) {
            $(element).select2({
                width: '100%',
                placeholder: "Pilih item",
            });
        }

        function addNewRow(item = null) {
            const template = document.getElementById('item-row-template').innerHTML;
            const newRowHtml = template.replace(/__INDEX__/g, itemIndex);
            const newRow = $(newRowHtml);
            $('#items-table tbody').append(newRow);
            
            const select = newRow.find('.item-select');
            initializeSelect2(select);

            if (item) {
                select.val(item.item_id).trigger('change');
                newRow.find('.quantity-input').val(item.quantity);
                newRow.find('.koli-input').val(item.koli);
                newRow.find('input[name*="description"]').val(item.description);
            }

            itemIndex++;
        }

        // Add existing items
        let existingItems = @json(old('items', $transferRequest->items));
        if (existingItems && existingItems.length > 0) {
            existingItems.forEach(function(item) {
                addNewRow(item);
            });
        } else {
            addNewRow();
        }

        $('#add-item-btn').on('click', function() { addNewRow(); });
        $('#items-table').on('click', '.remove-item-btn', function() { $(this).closest('tr').remove(); });

        // Function to handle AJAX calculation
        function calculateItemValues(row, changedField) {
            let itemId = row.find('.item-select').val();
            let quantityInput = row.find('.quantity-input');
            let koliInput = row.find('.koli-input');
            let data = {
                _token: '{{ csrf_token() }}',
                item_id: itemId
            };

            if (changedField === 'quantity') {
                data.quantity = parseFloat(quantityInput.val()) || 0;
            } else if (changedField === 'koli') {
                data.koli = parseFloat(koliInput.val()) || 0;
            } else {
                return; // No relevant field changed
            }

            if (!itemId) {
                // If no item is selected, clear the other field and return
                if (changedField === 'quantity') {
                    koliInput.val('');
                } else if (changedField === 'koli') {
                    quantityInput.val('');
                }
                return;
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
                error: function(xhr) {
                    console.error('Error calculating item values:', xhr);
                    toastr.error('Gagal menghitung nilai item.', 'Error');
                }
            });
        }

        // Event listener for quantity input and item select change
        $('#items-table').on('input', '.quantity-input', function() {
            calculateItemValues($(this).closest('tr'), 'quantity');
        });

        // Event listener for koli input
        $('#items-table').on('input', '.koli-input', function() {
            calculateItemValues($(this).closest('tr'), 'koli');
        });

        // Event listener for item select change (to trigger recalculation if item changes)
        $('#items-table').on('change', '.item-select', function() {
            let row = $(this).closest('tr');
            // If quantity has a value, recalculate koli based on new item
            if (parseFloat(row.find('.quantity-input').val()) > 0) {
                calculateItemValues(row, 'quantity');
            } else if (parseFloat(row.find('.koli-input').val()) > 0) {
                // If koli has a value, recalculate quantity based on new item
                calculateItemValues(row, 'koli');
            }
        });

        // Initialize select2 for main warehouse selects
        $(`[data-control='select2']`).select2();

        // Form submission with AJAX
        $('#transfer-request-form').on('submit', function(e) {
            e.preventDefault();
            var form = $(this);

            Swal.fire({
                text: "Apakah Anda yakin ingin menyimpan perubahan?",
                icon: "question",
                showCancelButton: true,
                buttonsStyling: false,
                confirmButtonText: "Ya, Simpan!",
                cancelButtonText: "Tidak, Batalkan",
                customClass: { confirmButton: "btn fw-bold btn-primary", cancelButton: "btn fw-bold btn-active-light-primary" }
            }).then(function(result) {
                if (result.value) {
                    $.ajax({
                        url: form.attr('action'),
                        type: 'POST', // Using POST for PUT due to form method spoofing
                        data: form.serialize(),
                        success: function(response) {
                             Swal.fire({
                                    text: "Data berhasil diubah!",
                                    icon: "success",
                                    buttonsStyling: false,
                                    confirmButtonText: "Lanjutkan",
                                    customClass: {
                                        confirmButton: "btn btn-primary"
                                    }
                                }).then(function (result) {
                                    if (result.isConfirmed) {
                                        let redirectUrl = "{{ route('admin.transfergudang.permintaan-terkirim.index') }}";
                                        if (response && response.redirect_url) {
                                            redirectUrl = response.redirect_url;
                                        }
                                        window.location.href = redirectUrl;
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
                                    if(key.includes('.')) {
                                        const parts = key.split('.');
                                        field = $('[name="items['+parts[1]+']['+parts[2]+']"]');
                                    }
                                    field.addClass('is-invalid').after('<div class="invalid-feedback">' + value[0] + '</div>');
                                });
                                toastr.error('Silakan perbaiki error validasi yang ada.', 'Validasi Gagal');
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
