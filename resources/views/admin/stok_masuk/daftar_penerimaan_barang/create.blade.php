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
                <form action="{{ route('admin.stok-masuk.daftar-penerimaan-barang.store') }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-12 mb-5">
                            <label class="form-label fs-6 fw-bolder text-dark">Kode Dokumen</label>
                            <input type="text" name="code" class="form-control form-control-solid"
                                value="{{ $newCode }}" readonly>
                        </div>
                        <div class="col-md-12 mb-5">
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
                        <div class="col-md-12 mb-5">
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
                        <div class="col-md-12 mb-5">
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
                let newRow = `
                    <tr data-index="${itemIndex}">
                        <td>
                            <select name="items[${itemIndex}][item_id]" class="form-select item-select" data-control="select2" required>
                                <option></option>
                                @foreach ($items as $item)
                                    <option value="{{ $item->id }}">{{ $item->nama_barang }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td><input type="number" name="items[${itemIndex}][quantity]" class="form-control" value="1" min="1" required></td>
                        <td><input type="number" name="items[${itemIndex}][koli]" class="form-control" value="0" min="0"></td>
                        <td><button type="button" class="btn btn-danger btn-sm remove-item-btn">X</button></td>
                    </tr>`;
                $('#items-table tbody').append(newRow);
                initializeSelect2($('tr[data-index="' + itemIndex + '"] .item-select'));
                itemIndex++;
            }

            $('#add-item-btn').click(function() {
                addNewRow();
            });

            $('#items-table').on('click', '.remove-item-btn', function() {
                $(this).closest('tr').remove();
            });

            // Tambah baris pertama secara default
            addNewRow();
        });
    </script>
@endpush
