@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
@endpush

@push('toolbar')
    @include('layouts.partials._toolbar', [
        'title' => 'Kartu Stok',
        'breadcrumbs' => ['Admin', 'Manajemen Stok', 'Kartu Stok'],
    ])
@endpush

@section('content')
    <div class="content flex-row-fluid" id="kt_content">
        <div class="card">
            <div class="card-header border-0 pt-6">
                <div class="card-title">
                    <div class="d-flex align-items-center position-relative my-1">
                        <span class="svg-icon svg-icon-1 position-absolute ms-6">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none">
                                <rect opacity="0.5" x="17.0365" y="15.1223" width="8.15546" height="2" rx="1"
                                    transform="rotate(45 17.0365 15.1223)" fill="black"></rect>
                                <path
                                    d="M11 19C6.55556 19 3 15.4444 3 11C3 6.55556 6.55556 3 11 3C15.4444 3 19 6.55556 19 11C19 15.4444 15.4444 19 11 19ZM11 5C7.53333 5 5 7.53333 5 11C5 14.4667 7.53333 17 11 17C14.4667 17 17 14.4667 17 11C17 7.53333 14.4667 5 11 5Z"
                                    fill="black"></path>
                            </svg>
                        </span>
                        <input type="text" id="search_input" class="form-control form-control-solid w-250px ps-15"
                            placeholder="Cari Stok">
                    </div>
                </div>
                <div class="card-toolbar">
                    <!--begin::Filter-->
                    <button type="button" class="btn btn-light-primary me-3" data-kt-menu-trigger="click"
                        data-kt-menu-placement="bottom-end">
                        <span class="svg-icon svg-icon-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none">
                                <path
                                    d="M19.0759 3H4.72777C3.95892 3 3.47768 3.83148 3.86067 4.49814L8.56967 12.6949C9.17923 13.7559 9.5 14.9582 9.5 16.1819V19.5072C9.5 20.2189 10.2223 20.7028 10.8805 20.432L13.8805 19.1977C14.2553 19.0435 14.5 18.6783 14.5 18.273V13.8372C14.5 12.8089 14.8171 11.8056 15.408 10.964L19.8943 4.57465C20.3596 3.912 19.8856 3 19.0759 3Z"
                                    fill="black" />
                            </svg>
                        </span>
                        Filter
                    </button>
                    <div class="menu menu-sub menu-sub-dropdown w-300px w-md-325px" data-kt-menu="true"
                        id="kt-toolbar-filter">
                        <div class="px-7 py-5">
                            <div class="fs-4 text-dark fw-bolder">Filter Options</div>
                        </div>
                        <div class="separator border-gray-200"></div>
                        <div class="px-7 py-5">
                            <div class="mb-10">
                                <label class="form-label fs-5 fw-bold mb-3">Gudang:</label>
                                <select class="form-select form-select-solid fw-bolder" data-kt-select2="true"
                                    id="warehouse_filter" data-dropdown-parent="#kt-toolbar-filter">
                                    <option value="">Semua Gudang</option>
                                    @foreach($warehouses as $warehouse)
                                        <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-10">
                                <label class="form-label fs-5 fw-bold mb-3">Tanggal:</label>
                                <input class="form-control form-control-solid" placeholder="Pilih Rentang Tanggal"
                                    id="date_filter" />
                            </div>
                            <div class="d-flex justify-content-end">
                                <button type="reset" class="btn btn-light btn-active-light-primary me-2"
                                    data-kt-menu-dismiss="true">Batal</button>
                                <button type="button" class="btn btn-primary" id="apply_filter">Submit</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body pt-4">
                <div class="text-center mb-5">
                    <h3 class="mb-0">Kartu Stok</h3>
                    <small id="filter-info" class="text-muted"></small>
                </div>
                <div class="dataTables_wrapper dt-bootstrap4 no-footer">
                    <div class="table-responsive min-h-500px">
                        <table class="table align-middle table-row-dashed fs-6 gy-5 dataTable no-footer" id="table-on-page">
                            <thead>
                                <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                                    <th class="min-w-125px sorting">Tanggal</th>
                                    <th class="min-w-125px sorting">Kode</th>
                                    <th class="min-w-125px sorting">Nama Item</th>
                                    <th class="min-w-125px sorting">Nama Gudang</th>
                                    <th class="min-w-125px sorting">Masuk</th>
                                    <th class="min-w-125px sorting">Keluar</th>
                                    <th class="min-w-125px sorting">Saldo</th>
                                </tr>
                            </thead>
                            <tbody class="fw-bold text-gray-600">
                                <!-- Data will be loaded by DataTables Ajax -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        var table;
        $(document).ready(function() {
            $('#date_filter').flatpickr({
                mode: "range",
                dateFormat: "Y-m-d",
            });

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

            function loadDataTable() {
                var dateFilter = $('#date_filter').val();
                $('#filter-info').text(dateFilter ? `Periode: ${dateFilter}` : 'Menampilkan semua data');

                if ($.fn.DataTable.isDataTable('#table-on-page')) {
                    $('#table-on-page').DataTable().destroy();
                }

                table = $('#table-on-page').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: "{{ route('admin.manajemenstok.kartustok.index') }}", // Placeholder, needs to be created
                        type: "GET",
                        data: function(d) {
                            d.search.value = $('#search_input').val();
                            d.date_filter = $('#date_filter').val();
                            d.warehouse_filter = $('#warehouse_filter').val();
                        }
                    },
                    columns: [
                        { data: 'date', name: 'date' },
                        { data: 'reference', name: 'reference' },
                        { data: 'sku_name', name: 'sku_name' },
                        { data: 'warehouse_name', name: 'warehouse_name' },
                        { data: 'stock_in', name: 'stock_in', searchable: false },
                        { data: 'stock_out', name: 'stock_out', searchable: false },
                        { data: 'balance', name: 'balance', searchable: false }
                    ],
                    order: [[0, 'desc']], // Default order by date descending
                    columnDefs: [
                        {
                            targets: [1, 2, 3], // Kode, Nama Item, Nama Gudang columns
                            render: function(data, type, row) {
                                return data ? data : '-';
                            }
                        },
                        {
                            targets: [4, 5, 6], // Masuk, Keluar, Saldo columns
                            render: function(data, type, row) {
                                return data ? parseFloat(data).toLocaleString('id-ID') : '0';
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
                    timeoutId = window.setTimeout(() => {
                        callback.apply(null, args);
                    }, wait);
                };
            }

            $('#search_input').on('keyup', debounce(function() {
                table.draw();
            }, 500));
        });
    </script>
@endpush
