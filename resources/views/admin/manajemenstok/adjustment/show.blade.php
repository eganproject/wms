@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Detail Penyesuaian Stok</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Kode Penyesuaian:</label>
                        <p>{{ $adjustment->code }}</p>
                    </div>
                    <div class="form-group">
                        <label>Tanggal Penyesuaian:</label>
                        <p>{{ $adjustment->adjustment_date }}</p>
                    </div>
                    <div class="form-group">
                        <label>Gudang:</label>
                        <p>{{ $adjustment->warehouse->name }}</p>
                    </div>
                    <div class="form-group">
                        <label>Dibuat Oleh:</label>
                        <p>{{ $adjustment->user->name }} ({{ $adjustment->user->jabatan->name ?? 'N/A' }})</p>
                    </div>
                    <div class="form-group">
                        <label>Catatan:</label>
                        <p>{{ $adjustment->notes ?? '-' }}</p>
                    </div>
                    <div class="form-group">
                        <label>Status:</label>
                        <p>{{ ucfirst($adjustment->status) }}</p>
                    </div>

                    <h4>Detail Item</h4>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Item SKU</th>
                                <th>Nama Item</th>
                                <th>Kuantitas</th>
                                <th>Koli</th>
                                <th>UOM</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($adjustment->adjustmentItems as $item)
                                <tr>
                                    <td>{{ $item->item->sku }}</td>
                                    <td>{{ $item->item->name }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>{{ $item->koli }}</td>
                                    <td>{{ $item->uom->name }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">
                    <a href="{{ route('admin.manajemenstok.adjustment.index') }}" class="btn btn-secondary">Kembali</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
