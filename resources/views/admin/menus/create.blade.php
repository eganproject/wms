@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Tambah Menu Baru</h3>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <form action="{{ route('menus.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="name" class="form-label">Nama Menu</label>
                            <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
                        </div>
                        <div class="mb-3">
                            <label for="url" class="form-label">URL</label>
                            <input type="text" class="form-control" id="url" name="url" value="{{ old('url') }}">
                        </div>
                        <div class="mb-3">
                            <label for="icon" class="form-label">Ikon</label>
                            <input type="text" class="form-control" id="icon" name="icon" value="{{ old('icon') }}">
                        </div>
                        <div class="mb-3">
                            <label for="parent_id" class="form-label">Menu Induk</label>
                            <select class="form-control" id="parent_id" name="parent_id">
                                <option value="">Pilih Menu Induk</option>
                                @foreach ($parentMenus as $parentMenu)
                                    <option value="{{ $parentMenu->id }}" {{ old('parent_id') == $parentMenu->id ? 'selected' : '' }}>{{ $parentMenu->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="order" class="form-label">Urutan</label>
                            <input type="number" class="form-control" id="order" name="order" value="{{ old('order') }}" required>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" {{ old('is_active') ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">Aktif</label>
                        </div>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection