<tr>
    <td>{{ str_repeat('--', $level) }} {{ $menu->name }}</td>
    @foreach ($jabatans as $jabatan)
        @php
            $permission = $permissions->get($jabatan->id . '-' . $menu->id);
        @endphp
        <td><input type="checkbox" class="permission-checkbox" data-jabatan-id="{{ $jabatan->id }}" data-menu-id="{{ $menu->id }}" data-permission-type="can_create" {{ ($permission && $permission->can_create) ? 'checked' : '' }}></td>
        <td><input type="checkbox" class="permission-checkbox" data-jabatan-id="{{ $jabatan->id }}" data-menu-id="{{ $menu->id }}" data-permission-type="can_read" {{ ($permission && $permission->can_read) ? 'checked' : '' }}></td>
        <td><input type="checkbox" class="permission-checkbox" data-jabatan-id="{{ $jabatan->id }}" data-menu-id="{{ $menu->id }}" data-permission-type="can_edit" {{ ($permission && $permission->can_edit) ? 'checked' : '' }}></td>
        <td><input type="checkbox" class="permission-checkbox" data-jabatan-id="{{ $jabatan->id }}" data-menu-id="{{ $menu->id }}" data-permission-type="can_delete" {{ ($permission && $permission->can_delete) ? 'checked' : '' }}></td>
        <td><input type="checkbox" class="permission-checkbox" data-jabatan-id="{{ $jabatan->id }}" data-menu-id="{{ $menu->id }}" data-permission-type="can_approve" {{ ($permission && $permission->can_approve) ? 'checked' : '' }}></td>
    @endforeach
</tr>
@if ($menu->children->isNotEmpty())
    @foreach ($menu->children as $child)
        @include('admin.masterdata.permissions._menu_permission_row', ['menu' => $child, 'level' => $level + 1, 'jabatans' => $jabatans, 'permissions' => $permissions])
    @endforeach
@endif
