@extends('layouts.app')

@section('content')
    <div class="content flex-row-fluid" id="kt_content">
        <!--begin::Card-->
        <div class="card">
            <!--begin::Card header-->
            <div class="card-header border-0 pt-6">
                <!--begin::Card title-->
                <div class="card-title">
                    <!--begin::Search-->
                    <div class="d-flex align-items-center position-relative my-1">
                        <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-5">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        <input type="text" data-kt-customer-table-filter="search" 
                            class="form-control form-control-solid w-250px ps-12" placeholder="Cari Penyesuaian...">
                    </div>
                    <!--end::Search-->
                </div>
                <!--begin::Card title-->

                <!--begin::Card toolbar-->
                <div class="card-toolbar">
                    <!--begin::Toolbar-->
                    <div class="d-flex justify-content-end">
                        <!--begin::Filter-->
                        <div class="me-3">
                            <select class="form-select form-select-solid" data-control="select2" 
                                data-placeholder="Status" data-hide-search="true" id="statusFilter">
                                <option value="">Semua Status</option>
                                <option value="pending">Pending</option>
                                <option value="completed">Selesai</option>
                            </select>
                        </div>
                        <!--end::Filter-->

                        <!--begin::Filter-->
                        <div class="me-3">
                            <input class="form-control form-control-solid" 
                                placeholder="Pilih Tanggal" id="dateFilter"/>
                        </div>
                        <!--end::Filter-->

                        <!--begin::Add customer-->
                        <a href="{{ route('admin.manajemenstok.adjustment.create') }}" class="btn btn-primary">
                            <i class="ki-duotone ki-plus fs-2"></i>
                            Tambah Penyesuaian
                        </a>
                        <!--end::Add customer-->
                    </div>
                    <!--end::Toolbar-->
                </div>
                <!--end::Card toolbar-->
            </div>
            <!--end::Card header-->

            <!--begin::Card body-->
            <div class="card-body pt-0">
                <!--begin::Table-->
                <table class="table align-middle table-row-dashed fs-6 gy-5" id="adjustment_table">
                    <thead>
                        <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                            <th>Kode</th>
                            <th>Tanggal</th>
                            <th>Gudang</th>
                            <th>Dibuat Oleh</th>
                            <th>Item</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="fw-semibold text-gray-600">
                    </tbody>
                </table>
                <!--end::Table-->
            </div>
            <!--end::Card body-->
        </div>
        <!--end::Card-->
    </div>
@endsection

@push('scripts')
<script>
"use strict";

var KTAdjustmentsList = function () {
    // Private variables
    var table;
    var searchInput;
    var statusFilter;
    var dateFilter;
    var dateRangePicker;

    // Private functions
    var initDatatable = function () {
        table = $("#adjustment_table").DataTable({
            searchDelay: 500,
            serverSide: true,
            processing: true,
            order: [[1, 'desc']],
            ajax: {
                url: '{{ route('admin.manajemenstok.adjustment.index') }}',
                type: 'GET',
                data: function(data) {
                    data.status = $('#statusFilter').val();
                    data.date = $('#dateFilter').val();
                }
            },
            columns: [
                // Kode
                { 
                    data: 'code',
                    name: 'adj.code',
                    render: function(data, type, row) {
                        return `<span class="fw-bold">${data}</span>`;
                    }
                },
                // Tanggal
                {
                    data: 'adjustment_date',
                    name: 'adj.adjustment_date',
                    render: function(data) {
                        return moment(data).format('DD/MM/YYYY');
                    }
                },
                // Gudang
                { 
                    data: 'warehouse_name',
                    name: 'w.name'
                },
                // Dibuat Oleh
                { 
                    data: 'user_name',
                    name: 'u.name'
                },
                // Item
                {
                    data: 'items_name',
                    name: 'items_name',
                    orderable: false,
                    searchable: false
                },
                // Status
                {
                    data: 'status',
                    name: 'adj.status',
                    render: function(data) {
                        let badgeClass = {
                            'pending': 'badge-light-warning',
                            'completed': 'badge-light-success'
                        }[data] || 'badge-light-primary';

                        let statusText = {
                            'pending': 'Pending',
                            'completed': 'Selesai'
                        }[data] || data;

                        return `<span class="badge ${badgeClass} fw-bold">${statusText}</span>`;
                    }
                },
                // Actions
                {
                    data: null,
                    className: 'text-end',
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        let buttons = `
                            <a href="/admin/manajemenstok/adjustment/${row.id}" 
                               class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1"
                               title="Detail">
                                <i class="ki-duotone ki-eye fs-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                </i>
                            </a>`;

                        if (row.status === 'pending') {
                            buttons += `
                                <a href="/admin/manajemenstok/adjustment/${row.id}/edit" 
                                   class="btn btn-icon btn-bg-light btn-active-color-success btn-sm me-1"
                                   title="Edit">
                                    <i class="ki-duotone ki-pencil fs-2">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i>
                                </a>
                                <button type="button"
                                        class="btn btn-icon btn-bg-light btn-active-color-success btn-sm me-1 btn-complete"
                                        data-id="${row.id}"
                                        title="Selesaikan">
                                    <i class="ki-duotone ki-check-circle fs-2">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i>
                                </button>
                                <button type="button"
                                        class="btn btn-icon btn-bg-light btn-active-color-danger btn-sm btn-delete"
                                        data-id="${row.id}"
                                        title="Hapus">
                                    <i class="ki-duotone ki-trash fs-2">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                        <span class="path3"></span>
                                        <span class="path4"></span>
                                        <span class="path5"></span>
                                    </i>
                                </button>`;
                        }

                        return buttons;
                    }
                }
            ],
            // Layout
            dom: `<'row'<'col-sm-12'tr>>
                 <'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>`,
            // Responsive settings
            responsive: true,
            // Pagination settings
            pageLength: 10,
            // State save
            stateSave: false,
            // Localization
            language: {
                processing: `<div class="d-flex justify-content-center align-items-center">
                    <span class="spinner-border spinner-border-sm text-primary" role="status"></span>
                    <span class="text-gray-600 ms-2">Loading...</span>
                </div>`,
                search: "",
                searchPlaceholder: "Cari...",
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_ hingga _END_ dari _TOTAL_ data",
                infoEmpty: "Tidak ada data yang tersedia",
                loadingRecords: "Memuat data...",
                zeroRecords: "Tidak ada data yang cocok",
                emptyTable: "Tidak ada data di dalam tabel",
                paginate: {
                    first: `<i class="first"></i>`,
                    previous: `<i class="previous"></i>`,
                    next: `<i class="next"></i>`,
                    last: `<i class="last"></i>`
                }
            }
        });
    };

    var handleSearchDatatable = () => {
        searchInput = document.querySelector('[data-kt-customer-table-filter="search"]');
        
        searchInput?.addEventListener('keyup', function (e) {
            table.search(e.target.value).draw();
        });
    };

    var handleFilter = function () {
        // Status filter
        statusFilter = document.querySelector('#statusFilter');
        $(statusFilter).on('change', function() {
            table.draw();
        });

        // Date filter
        dateFilter = document.querySelector('#dateFilter');
        $(dateFilter).daterangepicker({
            autoUpdateInput: false,
            locale: {
                cancelLabel: 'Clear',
                format: 'DD/MM/YYYY',
                applyLabel: 'Terapkan',
                cancelLabel: 'Batal',
                fromLabel: 'Dari',
                toLabel: 'Sampai',
                customRangeLabel: 'Pilih Sendiri',
                daysOfWeek: ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'],
                monthNames: ['Januari','Februari','Maret','April','Mei','Juni',
                    'Juli','Agustus','September','Oktober','November','Desember']
            }
        });

        $(dateFilter).on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY'));
            table.draw();
        });

        $(dateFilter).on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
            table.draw();
        });
    };

    var handleDeleteRows = () => {
        // Delete button click handler
        $(document).on('click', '.btn-delete', function(e) {
            e.preventDefault();
            
            const id = $(this).data('id');
            
            Swal.fire({
                text: 'Apakah Anda yakin ingin menghapus data ini?',
                icon: "warning",
                showCancelButton: true,
                buttonsStyling: false,
                confirmButtonText: "Ya, hapus!",
                cancelButtonText: "Tidak, batal",
                customClass: {
                    confirmButton: "btn fw-bold btn-danger",
                    cancelButton: "btn fw-bold btn-active-light-primary"
                }
            }).then(function (result) {
                if (result.value) {
                    $.ajax({
                        url: `/admin/manajemenstok/adjustment/${id}`,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    text: response.message,
                                    icon: "success",
                                    buttonsStyling: false,
                                    confirmButtonText: "Ok",
                                    customClass: {
                                        confirmButton: "btn fw-bold btn-primary",
                                    }
                                }).then(function() {
                                    table.ajax.reload();
                                });
                            } else {
                                Swal.fire({
                                    text: response.message,
                                    icon: "error",
                                    buttonsStyling: false,
                                    confirmButtonText: "Ok",
                                    customClass: {
                                        confirmButton: "btn fw-bold btn-primary",
                                    }
                                });
                            }
                        },
                        error: function(xhr) {
                            Swal.fire({
                                text: xhr.responseJSON?.message || "Terjadi kesalahan saat menghapus data",
                                icon: "error",
                                buttonsStyling: false,
                                confirmButtonText: "Ok",
                                customClass: {
                                    confirmButton: "btn fw-bold btn-primary",
                                }
                            });
                        }
                    });
                }
            });
        });
    };

    var handleCompleteRows = () => {
        // Complete button click handler
        $(document).on('click', '.btn-complete', function(e) {
            e.preventDefault();
            
            const id = $(this).data('id');
            
            Swal.fire({
                text: 'Apakah Anda yakin ingin menyelesaikan penyesuaian ini?',
                icon: "warning",
                showCancelButton: true,
                buttonsStyling: false,
                confirmButtonText: "Ya, selesaikan!",
                cancelButtonText: "Tidak, batal",
                customClass: {
                    confirmButton: "btn fw-bold btn-success",
                    cancelButton: "btn fw-bold btn-active-light-primary"
                }
            }).then(function (result) {
                if (result.value) {
                    $.ajax({
                        url: `/admin/manajemenstok/adjustment/${id}/update-status`,
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            status: 'completed'
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    text: response.message,
                                    icon: "success",
                                    buttonsStyling: false,
                                    confirmButtonText: "Ok",
                                    customClass: {
                                        confirmButton: "btn fw-bold btn-primary",
                                    }
                                }).then(function() {
                                    table.ajax.reload();
                                });
                            } else {
                                Swal.fire({
                                    text: response.message,
                                    icon: "error",
                                    buttonsStyling: false,
                                    confirmButtonText: "Ok",
                                    customClass: {
                                        confirmButton: "btn fw-bold btn-primary",
                                    }
                                });
                            }
                        },
                        error: function(xhr) {
                            Swal.fire({
                                text: xhr.responseJSON?.message || "Terjadi kesalahan saat memperbarui status",
                                icon: "error",
                                buttonsStyling: false,
                                confirmButtonText: "Ok",
                                customClass: {
                                    confirmButton: "btn fw-bold btn-primary",
                                }
                            });
                        }
                    });
                }
            });
        });
    };

    // Public methods
    return {
        init: function () {
            initDatatable();
            handleSearchDatatable();
            handleFilter();
            handleDeleteRows();
            handleCompleteRows();
        }
    }
}();

// On document ready
KTUtil.onDOMContentLoaded(function () {
    KTAdjustmentsList.init();
});
</script>
@endpush
