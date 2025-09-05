@extends('layouts.app')

@push('toolbar')
    @include('layouts.partials._toolbar', [
        'title' => 'Tambah Penyesuaian Stok',
        'breadcrumbs' => ['Admin', 'Manajemen Stok', 'Penyesuaian Stok', 'Tambah'],
        'back' => route('admin.manajemenstok.adjustment.index')
    ])
@endpush

@section('content')
    <div class="content flex-row-fluid" id="kt_content">
        <div class="card">
            <form id="adjustment-form" method="POST" action="{{ route('admin.manajemenstok.adjustment.store') }}">
                @csrf
                <div class="card-body">
                    <div class="row mb-5">
                        <div class="col-md-6">
                            <div class="form-group mb-5">
                                <label class="required form-label">Kode</label>
                                <input type="text" class="form-control form-control-solid @error('code') is-invalid @enderror"
                                    name="code" value="{{ old('code', $newCode) }}" readonly />
                                @error('code')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <div class="form-group mb-5">
                                <label class="required form-label">Tanggal</label>
                                <input type="text" id="adjustment_date" name="adjustment_date"
                                    class="form-control form-control-solid @error('adjustment_date') is-invalid @enderror"
                                    value="{{ old('adjustment_date', now()->format('Y-m-d')) }}" />
                                @error('adjustment_date')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>
                            <div class="form-group mb-5">
                                <label class="required form-label">Gudang</label>
                                <select class="form-select form-select-solid @error('warehouse_id') is-invalid @enderror"
                                    name="warehouse_id" data-control="select2" data-placeholder="Pilih Gudang">
                                    <option></option>
                                    @foreach ($warehouses as $warehouse)
                                        <option value="{{ $warehouse->id }}"
                                            {{ old('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                                            {{ $warehouse->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('warehouse_id')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label class="form-label">Catatan</label>
                                <textarea class="form-control form-control-solid @error('notes') is-invalid @enderror" 
                                    name="notes" rows="3">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="separator mb-5"></div>

                    <div class="row">
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-5">
                                <h4 class="mb-0">Item Penyesuaian</h4>
                                <button type="button" class="btn btn-primary" id="add-item">
                                    <i class="fas fa-plus"></i> Tambah Item
                                </button>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-row-dashed align-middle" id="items-table">
                                    <thead>
                                        <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                                            <th>Item</th>
                                            <th>Satuan</th>
                                            <th>Qty</th>
                                            <th>Koli</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if (old('items'))
                                            @foreach (old('items') as $i => $item)
                                                <tr>
                                                    <td>
                                                        <select name="items[{{ $i }}][item_id]"
                                                            class="form-select form-select-solid item-select"
                                                            data-control="select2" data-placeholder="Pilih Item">
                                                            <option></option>
                                                            @foreach ($items as $itemOption)
                                                                <option value="{{ $itemOption->id }}"
                                                                    data-uom-id="{{ $itemOption->uom_id }}"
                                                                    {{ $item['item_id'] == $itemOption->id ? 'selected' : '' }}>
                                                                    {{ $itemOption->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @error("items.$i.item_id")
                                                            <div class="invalid-feedback d-block">
                                                                {{ $message }}
                                                            </div>
                                                        @enderror
                                                    </td>
                                                    <td>
                                                        <input type="hidden" name="items[{{ $i }}][uom_id]"
                                                            value="{{ $item['uom_id'] ?? '' }}"
                                                            class="uom-id-input" />
                                                        <span class="uom-text"></span>
                                                        @error("items.$i.uom_id")
                                                            <div class="invalid-feedback d-block">
                                                                {{ $message }}
                                                            </div>
                                                        @enderror
                                                    </td>
                                                    <td>
                                                        <input type="number" name="items[{{ $i }}][quantity]"
                                                            class="form-control form-control-solid w-150px"
                                                            value="{{ $item['quantity'] ?? '' }}" />
                                                        @error("items.$i.quantity")
                                                            <div class="invalid-feedback d-block">
                                                                {{ $message }}
                                                            </div>
                                                        @enderror
                                                    </td>
                                                    <td>
                                                        <input type="number" name="items[{{ $i }}][koli]"
                                                            class="form-control form-control-solid w-150px"
                                                            value="{{ $item['koli'] ?? '' }}" />
                                                        @error("items.$i.koli")
                                                            <div class="invalid-feedback d-block">
                                                                {{ $message }}
                                                            </div>
                                                        @enderror
                                                    </td>
                                                    <td>
                                                        <button type="button"
                                                            class="btn btn-icon btn-light-danger btn-sm delete-item">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end py-6">
                    <a href="{{ route('admin.manajemenstok.adjustment.index') }}"
                        class="btn btn-light me-3">Batal</a>
                    <button type="submit" class="btn btn-primary">
                        <span class="indicator-label">Simpan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <template id="item-row-template">
        <tr>
            <td>
                <select name="items[__index__][item_id]" class="form-select form-select-solid item-select"
                    data-control="select2" data-placeholder="Pilih Item">
                    <option></option>
                    @foreach ($items as $item)
                        <option value="{{ $item->id }}" data-uom-id="{{ $item->uom_id }}">
                            {{ $item->name }}
                        </option>
                    @endforeach
                </select>
            </td>
            <td>
                <input type="hidden" name="items[__index__][uom_id]" class="uom-id-input" />
                <span class="uom-text"></span>
            </td>
            <td>
                <input type="number" name="items[__index__][quantity]"
                    class="form-control form-control-solid w-150px" />
            </td>
            <td>
                <input type="number" name="items[__index__][koli]"
                    class="form-control form-control-solid w-150px" />
            </td>
            <td>
                <button type="button" class="btn btn-icon btn-light-danger btn-sm delete-item">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>
    </template>
@endsection

@push('scripts')
    <script>
        "use strict";

        // Class definition
        var KTAdjustmentForm = function() {
            // Shared variables
            var itemRowTemplate = document.getElementById('item-row-template');
            var itemsTable = document.getElementById('items-table');
            var itemIndex = {{ old('items') ? count(old('items')) : 0 }};

            // Private functions
            var initForm = function() {
                // Init date picker
                $("#adjustment_date").flatpickr({
                    dateFormat: "Y-m-d",
                });

                // Init select2
                $(document).find('[data-control="select2"]').select2();
            };

            var handleItemRow = function() {
                // Add new item row
                $('#add-item').on('click', function() {
                    let newRow = itemRowTemplate.content.cloneNode(true);
                    let selects = newRow.querySelectorAll('select');
                    let inputs = newRow.querySelectorAll('input');
                    let itemIndexRegex = /__index__/g;

                    // Update index
                    selects.forEach(select => {
                        select.name = select.name.replace(itemIndexRegex, itemIndex);
                    });

                    inputs.forEach(input => {
                        input.name = input.name.replace(itemIndexRegex, itemIndex);
                    });

                    itemsTable.querySelector('tbody').appendChild(newRow);
                    initSelect2();
                    itemIndex++;
                });

                // Delete item row
                $(document).on('click', '.delete-item', function() {
                    $(this).closest('tr').remove();
                });

                // Handle item selection
                $(document).on('change', '.item-select', function() {
                    let option = $(this).find('option:selected');
                    let uomId = option.data('uom-id');
                    let row = $(this).closest('tr');
                    row.find('.uom-id-input').val(uomId);
                    row.find('.uom-text').text(option.data('uom-name'));
                });
            };

            var initSelect2 = function() {
                $('.item-select').each(function() {
                    if (!$(this).data('select2')) {
                        $(this).select2({
                            placeholder: "Pilih Item"
                        });
                    }
                });
            };

            // Public methods
            return {
                init: function() {
                    initForm();
                    handleItemRow();
                    initSelect2();
                }
            };
        }();

        // On document ready
        KTUtil.onDOMContentLoaded(function() {
            KTAdjustmentForm.init();
        });
            addItemRow();
        });

        $(document).on('click', '.remove-item', function() {
            $(this).closest('tr').remove();
        });

        $(document).on('change', '.item-select', function() {
            loadUomsForItem($(this));
        });

        // Initial add a row if no old input
        if (itemIndex === 0) {
            addItemRow();
        }
    });
</script>
@endpush
