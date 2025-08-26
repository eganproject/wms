@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Manajemen Menu</h3>
                    <div class="card-toolbar">
                        <a href="{{ route('menus.create') }}" class="btn btn-primary">Tambah Menu</a>
                    </div>
                </div>
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>URL</th>
                                <th>Ikon</th>
                                <th>Menu Induk</th>
                                <th>Urutan</th>
                                <th>Aktif</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($menus as $menu)
                                <tr>
                                    <td>{{ $menu->name }}</td>
                                    <td>{{ $menu->url }}</td>
                                    <td>{{ $menu->icon }}</td>
                                    <td>{{ $menu->parent->name ?? '-' }}</td>
                                    <td>{{ $menu->order }}</td>
                                    <td>
                                        @if ($menu->is_active)
                                            <span class="badge badge-success">Ya</span>
                                        @else
                                            <span class="badge badge-danger">Tidak</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('menus.edit', $menu->id) }}" class="btn btn-sm btn-primary">Edit</a>
                                        <form action="{{ route('menus.destroy', $menu->id) }}" method="POST" style="display: inline-block;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus menu ini?')">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection