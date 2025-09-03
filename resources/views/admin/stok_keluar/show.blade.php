@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Detail Pengeluaran Barang #{{ $pengeluaranBarang->id }}</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Tanggal:</strong> {{ $pengeluaranBarang->stock_out_date->format('d F Y') }}</p>
                    <p><strong>Gudang:</strong> {{ $pengeluaranBarang->warehouse->name }}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Dibuat Oleh:</strong> {{ $pengeluaranBarang->user->name }}</p>
                    <p><strong>Status:</strong> <span class="badge bg-success">{{ $pengeluaranBarang->status }}</span></p>
                </div>
            </div>
            @if($pengeluaranBarang->notes)
            <div class="row mt-3">
                <div class="col-12">
                    <p><strong>Catatan:</strong></p>
                    <p>{{ $pengeluaranBarang->notes }}</p>
                </div>
            </div>
            @endif

            <hr>

            <h5>Item yang Dikeluarkan</h5>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>SKU</th>
                            <th>Item</th>
                            <th>Quantity</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pengeluaranBarang->items as $index => $itemOut)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $itemOut->item->sku }}</td>
                            <td>{{ $itemOut->item->name }}</td>
                            <td>{{ $itemOut->quantity }} {{ $itemOut->item->uom->name }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <a href="{{ route('admin.stok-keluar.pengeluaran-barang.index') }}" class="btn btn-secondary mt-3">Kembali</a>
        </div>
    </div>
</div>
@endsection
