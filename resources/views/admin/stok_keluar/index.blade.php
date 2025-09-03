@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
@endpush

@push('toolbar')
    @include('layouts.partials._toolbar', [
        'title' => 'Pengeluaran Barang',
        'breadcrumbs' => ['Admin', 'Stok Keluar', 'Pengeluaran Barang'],
    ])
@endpush

@section('content')
    <div class="content flex-row-fluid" id="kt_content">
        <div class="card">
            <div class="card-header border-0 pt-6">
                <div class="card-title">
                    <div class="d-flex align-items-center position-relative my-1">
                        <span class="svg-icon svg-icon-1 position-absolute ms-6">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                <rect opacity="0.5" x="17.0365" y="15.1223" width="8.15546" height="2" rx="1" transform="rotate(45 17.0365 15.1223)" fill="black"></rect>
                                <path d="M11 19C6.55556 19 3 15.4444 3 11C3 6.55556 6.55556 3 11 3C15.4444 3 19 6.55556 19 11C19 15.4444 15.4444 19 11 19ZM11 5C7.53333 5 5 7.53333 5 11C5 14.4667 7.53333 17 11 17C14.4667 17 17 14.4667 17 11C17 7.53333 14.4667 5 11 5Z" fill="black"></path>
                            </svg>
                        </span>
                        <input type="text" id="search_input" class="form-control form-control-solid w-250px ps-15" placeholder="Cari Pengeluaran Barang">
                    </div>
                </div>
                <div class="card-toolbar">
                    <div class="d-flex justify-content-end" data-kt-customer-table-toolbar="base">
                        <button type="button" class="btn btn-light-primary me-3" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                            <span class="svg-icon svg-icon-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                    <path d="M19.0759 3H4.72777C3.95892 3 3.47768 3.83148 3.86067 4.49814L8.56967 12.6949C9.17923 13.7559 9.5 14.9582 9.5 16.1819V19.5072C9.5 20.2189 10.2223 20.7028 10.8805 20.432L13.8805 19.1977C14.2553 19.0435 14.5 18.6783 14.5 18.273V13.8372C14.5 12.8089 14.8171 11.8056 15.408 10.964L19.8943 4.57465C20.3596 3.912 19.8856 3 19.0759 3Z" fill="black" />
                                </svg>
                            </span>
                            Filter
                        </button>
                        <div class="menu menu-sub menu-sub-dropdown w-300px w-md-325px" data-kt-menu="true" id="kt-toolbar-filter">
                            <div class="px-7 py-5">
                                <div class="fs-4 text-dark fw-bolder">Filter Options</div>
                            </div>
                            <div class="separator border-gray-200"></div>
                            <div class="px-7 py-5">
                                <div class="mb-10">
                                    <label class="form-label fs-5 fw-bold mb-3">Gudang:</label>
                                    <select class="form-select form-select-solid fw-bolder" data-kt-select2="true" id="warehouse_filter" data-dropdown-parent="#kt-toolbar-filter">
                                        <option value="semua">Semua</option>
                                        @foreach ($warehouses as $warehouse)
                                            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-10">
                                    <label class="form-label fs-5 fw-bold mb-3">Status:</label>
                                    <select class="form-select form-select-solid fw-bolder" data-kt-select2="true" id="status_filter" data-dropdown-parent="#kt-toolbar-filter">
                                        <option value="semua">Semua</option>
                                        <option value="completed">Completed</option>
                                        <option value="pending">Pending</option>
                                    </select>
                                </div>
                                <div class="mb-10">
                                    <label class="form-label fs-5 fw-bold mb-3">Tanggal:</label>
                                    <input class="form-control form-control-solid" placeholder="Pilih Tanggal" id="date_filter" />
                                </div>
                                <div class="d-flex justify-content-end">
                                    <button type="reset" class="btn btn-light btn-active-light-primary me-2" data-kt-menu-dismiss="true">Batal</button>
                                    <button type="button" class="btn btn-primary" id="apply_filter">Submit</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end">
                        <a href="{{ route('admin.stok-keluar.pengeluaran-barang.create') }}" class="btn btn-primary">Buat Pengeluaran</a>
                    </div>
                </div>
            </div>
            <div class="card-body pt-4">
                <div class="text-center mb-5">
                    <h3 class="mb-0">Daftar Pengeluaran Barang</h3>
                    <small id="filter-info" class="text-muted"></small>
                </div>
                <div class="dataTables_wrapper dt-bootstrap4 no-footer">
                    <div class="table-responsive min-h-500px">
                        <table class="table align-middle table-row-dashed fs-6 gy-5 dataTable no-footer" id="table-on-page">
                            <thead>
                                <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                                    <th class="min-w-100px sorting">ID</th>
                                    <th class="min-w-125px sorting">Tanggal</th>
                                    <th class="min-w-125px sorting">Gudang</th>
                                    <th class="min-w-125px sorting">Dibuat Oleh</th>
                                    <th class="min-w-100px sorting">Status</th>
                                    <th class="text-center min-w-125px sorting_disabled">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="fw-bold text-gray-600">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        var table; 
        $(document).ready(function() {
            $('#date_filter').flatpickr({
                dateFormat: "Y-m-d",
                defaultDate: new Date()
            });

            toastr.options = {
                "closeButton": true,
                "progressBar": true,
                "positionClass": "toast-top-center",
            };

            @if (Session::has('success'))
                toastr.success("{{ session('success') }}");
            @endif

            @if (Session::has('error'))
                toastr.error("{{ session('error') }}");
            @endif

            function loadDataTable() {
                var warehouseFilter = $('#warehouse_filter').val();
                var statusFilter = $('#status_filter').val();
                var dateFilter = $('#date_filter').val();

                var warehouseText = $('#warehouse_filter option:selected').text();
                var statusText = $('#status_filter option:selected').text();

                let filterInfoText = `${dateFilter} | Gudang: ${warehouseText} | Status: ${statusText}`;
                $('#filter-info').text(filterInfoText);

                if ($.fn.DataTable.isDataTable('#table-on-page')) {
                    $('#table-on-page').DataTable().destroy();
                }

                table = $('#table-on-page').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: "{{ route('admin.stok-keluar.pengeluaran-barang.index') }}",
                        type: "GET",
                        data: function(d) {
                            d.search.value = $('#search_input').val();
                            d.warehouse_id = warehouseFilter;
                            d.status = statusFilter;
                            d.date = dateFilter;
                        }
                    },
                    drawCallback: function(settings) {
                        KTMenu.createInstances();
                    },
                    columns: [
                        { data: 'id', name: 'id' },
                        { data: 'stock_out_date', name: 'stock_out_date' },
                        { data: 'warehouse.name', name: 'warehouse.name' },
                        { data: 'user.name', name: 'user.name' },
                        { data: 'status', name: 'status' },
                        { data: 'id', name: 'id', orderable: false, searchable: false },
                    ],
                    order: [[0, 'desc']],
                    columnDefs: [
                        {
                            targets: 1, // Date column
                            render: function(data, type, row) {
                                const d = new Date(data);
                                const day = ('0' + d.getDate()).slice(-2);
                                const month = d.toLocaleString('en-GB', { month: 'short' });
                                const year = d.getFullYear();
                                return `${day} ${month} ${year}`;
                            }
                        },
                        {
                            targets: 4, // Status column
                            render: function(data, type, row) {
                                let badgeClass = 'primary';
                                if (data === 'completed') badgeClass = 'success';
                                return `<span class="badge badge-light-${badgeClass}">${data}</span>`;
                            }
                        },
                        {
                            targets: 5, // Actions column
                            render: function(data, type, row) {
                                let showUrl = "{{ route('admin.stok-keluar.pengeluaran-barang.show', ':id') }}".replace(':id', row.id);
                                let editUrl = "{{ route('admin.stok-keluar.pengeluaran-barang.edit', ':id') }}".replace(':id', row.id);
                                let destroyUrl = "{{ route('admin.stok-keluar.pengeluaran-barang.destroy', ':id') }}".replace(':id', row.id);
                                let csrfToken = "{{ csrf_token() }}";
                                return `
                                <a href="#" class="btn btn-sm btn-light btn-active-light-primary" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">Actions
                                    <span class="svg-icon svg-icon-5 m-0"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M11.4343 12.7344L7.25 8.55005C6.83579 8.13583 6.16421 8.13584 5.75 10.05L11.2929 15.5929C11.6834 15.9835 12.3166 15.9835 12.7071 15.5929L18.25 10.05C18.6642 9.63584 18.6642 8.96426 18.25 8.55005C17.8358 8.13584 17.1642 8.13584 16.75 8.55005L12.5657 12.7344C12.2533 13.0468 11.7467 13.0468 11.4343 12.7344Z" fill="black"></path></svg></span>
                                </a>
                                <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-bold fs-7 w-125px py-4" data-kt-menu="true">
                                    <div class="menu-item px-3"><a href="${showUrl}" class="menu-link px-3">Lihat</a></div>
                                    <div class="menu-item px-3"><a href="${editUrl}" class="menu-link px-3">Edit</a></div>
                                    <div class="menu-item px-3">
                                        <form class="form-delete" action="${destroyUrl}" method="POST">
                                            <input type="hidden" name="_token" value="${csrfToken}">
                                            <input type="hidden" name="_method" value="DELETE">
                                            <button type="submit" class="menu-link px-3 border-0 bg-transparent w-100 text-start" data-document-id="${row.id}">Hapus</button>
                                        </form>
                                    </div>
                                </div>`;
                            }
                        }
                    ],
                });
            }

            loadDataTable();

            $('#apply_filter').on('click', function() {
                loadDataTable();
                $('[data-kt-menu-dismiss="true"]').click();
            });

            const debounce = (callback, wait) => {
                let timeoutId = null;
                return (...args) => {
                    window.clearTimeout(timeoutId);
                    timeoutId = window.setTimeout(() => { callback.apply(null, args); }, wait);
                };
            }

            $('#search_input').on('keyup', debounce(() => table.draw(), 500));

            $('#table-on-page').on('submit', '.form-delete', function(e) {
                e.preventDefault();
                var form = $(this);
                var docId = form.find('button[data-document-id]').data('document-id');
                Swal.fire({
                    text: `Apakah yakin ingin menghapus dokumen #${docId}?`,
                    icon: "warning",
                    showCancelButton: true,
                    buttonsStyling: false,
                    confirmButtonText: "Ya, hapus!",
                    cancelButtonText: "Tidak, batalkan",
                    customClass: {
                        confirmButton: "btn fw-bold btn-danger",
                        cancelButton: "btn fw-bold btn-active-light-light"
                    }
                }).then(function(result) {
                    if (result.value) {
                        $.ajax({
                            url: form.attr('action'),
                            type: 'POST', 
                            data: form.serialize(),
                            success: function(response) {
                                toastr.success(`Dokumen #${docId} berhasil dihapus.`);
                                table.ajax.reload(null, false);
                            },
                            error: function(xhr) {
                                toastr.error(`Gagal menghapus dokumen #${docId}.`);
                            }
                        });
                    }
                });
            });
        });
    </script>
@endpush