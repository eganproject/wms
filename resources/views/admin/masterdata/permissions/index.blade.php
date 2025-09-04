@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
@endpush

@push('toolbar')
    @include('layouts.partials._toolbar', [
        'title' => 'Manajemen Hak Akses',
        'breadcrumbs' => ['Admin', 'Masterdata', 'Hak Akses'],
    ])
@endpush

@section('content')
    <div class="content flex-row-fluid" id="kt_content">
        <div class="card">

            <div class="card-body pt-4">

                <div class="dataTables_wrapper dt-bootstrap4 no-footer">

                    <div class="table-responsive min-h-500px">

                        <table class="table table-bordered table-striped">

                            <thead>

                                <tr>

                                    <th>Menu</th>

                                    @foreach ($jabatans as $jabatan)

                                        <th colspan="5" class="text-center">{{ $jabatan->name }}</th>

                                    @endforeach

                                </tr>

                                <tr>

                                    <th></th>

                                    @foreach ($jabatans as $jabatan)

                                        <th>Create</th>

                                        <th>Read</th>

                                        <th>Edit</th>

                                        <th>Delete</th>

                                        <th>Approve</th>

                                    @endforeach

                                </tr>

                            </thead>

                            <tbody>

                                @foreach ($menus as $menu)

                                    @include('admin.masterdata.permissions._menu_permission_row', [

                                        'menu' => $menu,

                                        'level' => 0,

                                        'jabatans' => $jabatans,

                                        'permissions' => $permissions,

                                    ])

                                @endforeach

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

        </div>

    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>

        document.addEventListener('DOMContentLoaded', function() {

            document.querySelectorAll('.permission-checkbox').forEach(function(checkbox) {

                checkbox.addEventListener('change', function() {

                    const jabatanId = this.dataset.jabatanId;

                    const menuId = this.dataset.menuId;

                    const permissionType = this.dataset.permissionType;

                    const isChecked = this.checked;


                    axios.post('{{ route('admin.masterdata.permissions.update') }}', {

                            jabatan_id: jabatanId,

                            menu_id: menuId,

                            permission_type: permissionType,

                            is_checked: isChecked

                        })

                        .then(function(response) {

                            toastr.success('Hak akses berhasil diperbarui');

                        })

                        .catch(function(error) {

                            console.error(error);

                            alert('Terjadi kesalahan saat memperbarui hak akses.');

                            // Revert checkbox state on error

                            checkbox.checked = !isChecked;

                        });

                });

            });

        });

    </script>


    <script>

        $(document).ready(function() {

            toastr.options = {

                "closeButton": true,

                "debug": false,

                "newestOnTop": false,

                "progressBar": true,

                "positionClass": "toast-top-center",

                "preventDuplicates": false,

                "showDuration": "300",

                "hideDuration": "1000",

                "timeOut": "5000",

                "extendedTimeOut": "1000",

                "showEasing": "swing",

                "hideEasing": "linear",

                "showMethod": "fadeIn",

                "hideMethod": "fadeOut"

            };


            @if (Session::has('success'))

                toastr.success("{{ session('success') }}");

            @endif


            @if (Session::has('error'))

                toastr.error("{{ session('error') }}");

            @endif

        });

    </script>

@endpush