@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Manajemen Hak Akses</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Jabatan / Menu</th>
                                    @foreach ($menus as $menu)
                                        <th colspan="5" class="text-center">{{ $menu->name }}</th>
                                    @endforeach
                                </tr>
                                <tr>
                                    <th></th>
                                    @foreach ($menus as $menu)
                                        <th>C</th>
                                        <th>R</th>
                                        <th>U</th>
                                        <th>D</th>
                                        <th>A</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($jabatans as $jabatan)
                                    <tr>
                                        <td>{{ $jabatan->name }}</td>
                                        @foreach ($menus as $menu)
                                            @php
                                                $menuPermissions = $permissions->get($jabatan->id . '-' . $menu->id, collect());
                                            @endphp
                                            <td>
                                                <input type="checkbox" class="permission-checkbox" data-jabatan-id="{{ $jabatan->id }}" data-menu-id="{{ $menu->id }}" data-permission-type="can_create" {{ $menuPermissions->contains('permission_type', 'create') ? 'checked' : '' }}>
                                            </td>
                                            <td>
                                                <input type="checkbox" class="permission-checkbox" data-jabatan-id="{{ $jabatan->id }}" data-menu-id="{{ $menu->id }}" data-permission-type="can_read" {{ $menuPermissions->contains('permission_type', 'read') ? 'checked' : '' }}>
                                            </td>
                                            <td>
                                                <input type="checkbox" class="permission-checkbox" data-jabatan-id="{{ $jabatan->id }}" data-menu-id="{{ $menu->id }}" data-permission-type="can_update" {{ $menuPermissions->contains('permission_type', 'update') ? 'checked' : '' }}>
                                            </td>
                                            <td>
                                                <input type="checkbox" class="permission-checkbox" data-jabatan-id="{{ $jabatan->id }}" data-menu-id="{{ $menu->id }}" data-permission-type="can_delete" {{ $menuPermissions->contains('permission_type', 'delete') ? 'checked' : '' }}>
                                            </td>
                                            <td>
                                                <input type="checkbox" class="permission-checkbox" data-jabatan-id="{{ $jabatan->id }}" data-menu-id="{{ $menu->id }}" data-permission-type="can_approve" {{ $menuPermissions->contains('permission_type', 'approve') ? 'checked' : '' }}>
                                            </td>
                                        @endforeach
                                    </tr>
                                    @foreach ($menu->children as $childMenu)
                                        <tr>
                                            <td>-- {{ $childMenu->name }}</td>
                                            @foreach ($menus as $parentMenu)
                                                @if ($parentMenu->id == $menu->id)
                                                    @php
                                                        $childMenuPermissions = $permissions->get($jabatan->id . '-' . $childMenu->id, collect());
                                                    @endphp
                                                    <td>
                                                        <input type="checkbox" class="permission-checkbox" data-jabatan-id="{{ $jabatan->id }}" data-menu-id="{{ $childMenu->id }}" data-permission-type="can_create" {{ $childMenuPermissions->contains('permission_type', 'create') ? 'checked' : '' }}>
                                                    </td>
                                                    <td>
                                                        <input type="checkbox" class="permission-checkbox" data-jabatan-id="{{ $jabatan->id }}" data-menu-id="{{ $childMenu->id }}" data-permission-type="can_read" {{ $childMenuPermissions->contains('permission_type', 'read') ? 'checked' : '' }}>
                                                    </td>
                                                    <td>
                                                        <input type="checkbox" class="permission-checkbox" data-jabatan-id="{{ $jabatan->id }}" data-menu-id="{{ $childMenu->id }}" data-permission-type="can_update" {{ $childMenuPermissions->contains('permission_type', 'update') ? 'checked' : '' }}>
                                                    </td>
                                                    <td>
                                                        <input type="checkbox" class="permission-checkbox" data-jabatan-id="{{ $jabatan->id }}" data-menu-id="{{ $childMenu->id }}" data-permission-type="can_delete" {{ $childMenuPermissions->contains('permission_type', 'delete') ? 'checked' : '' }}>
                                                    </td>
                                                    <td>
                                                        <input type="checkbox" class="permission-checkbox" data-jabatan-id="{{ $jabatan->id }}" data-menu-id="{{ $childMenu->id }}" data-permission-type="can_approve" {{ $childMenuPermissions->contains('permission_type', 'approve') ? 'checked' : '' }}>
                                                    </td>
                                                @else
                                                    <td colspan="5"></td>
                                                @endif
                                            @endforeach
                                        </tr>
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.permission-checkbox').forEach(function (checkbox) {
            checkbox.addEventListener('change', function () {
                const jabatanId = this.dataset.jabatanId;
                const menuId = this.dataset.menuId;
                const permissionType = this.dataset.permissionType;
                const isChecked = this.checked;

                axios.post('{{ route('permissions.update') }}', {
                    jabatan_id: jabatanId,
                    menu_id: menuId,
                    permission_type: permissionType,
                    is_checked: isChecked
                })
                .then(function (response) {
                    console.log(response.data);
                })
                .catch(function (error) {
                    console.error(error);
                    alert('Terjadi kesalahan saat memperbarui hak akses.');
                    // Revert checkbox state on error
                    checkbox.checked = !isChecked;
                });
            });
        });
    });
</script>
@endpush
@endsection
