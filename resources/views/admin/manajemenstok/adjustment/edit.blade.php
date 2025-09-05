@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Edit Penyesuaian Stok</h3>
                </div>
                <form action="{{ route('admin.manajemenstok.adjustment.update', $adjustment->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="form-group">
                            <label for="code">Kode Penyesuaian</label>
                            <input type="text" class="form-control" id="code" name="code" value="{{ old('code', $adjustment->code) }}" readonly>
                        </div>
                        <div class="form-group">
                            <label for="adjustment_date">Tanggal Penyesuaian</label>
                            <input type="date" class="form-control" id="adjustment_date" name="adjustment_date" value="{{ old('adjustment_date', $adjustment->adjustment_date) }}" required>
                        </div>
                        <div class="form-group">
                            <label for="warehouse_id">Gudang</label>
                            <select class="form-control" id="warehouse_id" name="warehouse_id" required>
                                <option value="">Pilih Gudang</option>
                                @foreach($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}" {{ old('warehouse_id', $adjustment->warehouse_id) == $warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="notes">Catatan</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3">{{ old('notes', $adjustment->notes) }}</textarea>
                        </div>

                        <h4>Detail Item</h4>
                        <table class="table table-bordered" id="items-table">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Kuantitas</th>
                                    <th>Koli</th>
                                    <th>UOM</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($adjustment->adjustmentItems as $index => $item)
                                    <tr>
                                        <td>
                                            <select class="form-control item-select" name="items[{{ $index }}][item_id]" required>
                                                <option value="">Pilih Item</option>
                                                @foreach($items as $i)
                                                    <option value="{{ $i->id }}" {{ old('items.' . $index . '.item_id', $item->item_id) == $i->id ? 'selected' : '' }}>{{ $i->sku }} - {{ $i->name }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number" class="form-control" name="items[{{ $index }}][quantity]" value="{{ old('items.' . $index . '.quantity', $item->quantity) }}" required min="1">
                                        </td>
                                        <td>
                                            <input type="number" class="form-control" name="items[{{ $index }}][koli]" value="{{ old('items.' . $index . '.koli', $item->koli) }}" min="0">
                                        </td>
                                        <td>
                                            <select class="form-control uom-select" name="items[{{ $index }}][uom_id]" required>
                                                {{-- UOMs will be loaded via AJAX or pre-filled --}}
                                            </select>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-danger remove-item">Hapus</button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <button type="button" class="btn btn-success" id="add-item">Tambah Item</button>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">Simpan</button>
                        <a href="{{ route('admin.manajemenstok.adjustment.index') }}" class="btn btn-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        let itemIndex = {{ count($adjustment->adjustmentItems) }};

        function addItemRow(itemData = null) {
            let itemOptions = '';
            @foreach($items as $item)
                itemOptions += `<option value="{{ $item->id }}" ${itemData && itemData.item_id == {{ $item->id }} ? 'selected' : ''}>{{ $item->sku }} - {{ $item->name }}</option>`;
            @endforeach

            let newRow = `
                <tr>
                    <td>
                        <select class="form-control item-select" name="items[${itemIndex}][item_id]" required>
                            <option value="">Pilih Item</option>
                            ${itemOptions}
                        </select>
                    </td>
                    <td>
                        <input type="number" class="form-control" name="items[${itemIndex}][quantity]" value="${itemData ? itemData.quantity : ''}" required min="1">
                    </td>
                    <td>
                        <input type="number" class="form-control" name="items[${itemIndex}][koli]" value="${itemData ? itemData.koli : ''}" min="0">
                    </td>
                    <td>
                        <select class="form-control uom-select" name="items[${itemIndex}][uom_id]" required>
                            <option value="">Pilih UOM</option>
                        </select>
                    </td>
                    <td>
                        <button type="button" class="btn btn-danger remove-item">Hapus</button>
                    </td>
                </tr>
            `;
            $('#items-table tbody').append(newRow);
            itemIndex++;

            // Load UOMs for the newly added row
            if (itemData && itemData.item_id) {
                loadUomsForItem($('#items-table tbody tr:last-child .item-select'), itemData.uom_id);
            } else {
                loadUomsForItem($('#items-table tbody tr:last-child .item-select'));
            }
        }

        function loadUomsForItem(selectElement, selectedUomId = null) {
            let itemId = selectElement.val();
            let uomSelect = selectElement.closest('tr').find('.uom-select');
            uomSelect.empty().append('<option value="">Pilih UOM</option>');

            if (itemId) {
                let selectedItem = {!! json_encode($items) !!}.find(item => item.id == itemId);
                if (selectedItem && selectedItem.uoms) {
                    selectedItem.uoms.forEach(uom => {
                        uomSelect.append(`<option value="${uom.id}">${uom.name}</option>`);
                    });
                } else if (selectedItem && selectedItem.uom) {
                    uomSelect.append(`<option value="${selectedItem.uom.id}">${selectedItem.uom.name}</option>`);
                }

                if (selectedUomId) {
                    uomSelect.val(selectedUomId);
                }
            }
        }

        // Initial load for existing items
        @foreach($adjustment->adjustmentItems as $index => $item)
            loadUomsForItem($('select[name="items[{{ $index }}][item_id]"]'), {{ $item->uom_id }});
        @endforeach

        $('#add-item').on('click', function() {
            addItemRow();
        });

        $(document).on('click', '.remove-item', function() {
            $(this).closest('tr').remove();
        });

        $(document).on('change', '.item-select', function() {
            loadUomsForItem($(this));
        });
    });
</script>
@endpush
