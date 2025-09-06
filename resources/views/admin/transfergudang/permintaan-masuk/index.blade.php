@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
@endpush

@push('toolbar')
    @include('layouts.partials._toolbar', [
        'title' => 'Permintaan Transfer Masuk',
        'breadcrumbs' => ['Admin', 'Transfer Gudang', 'Permintaan Masuk'],
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
                            placeholder="Cari Permintaan Transfer">
                    </div>
                </div>
                <div class="card-toolbar">
                    <div class="d-flex justify-content-end" data-kt-customer-table-toolbar="base">
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
                            Filter</button>
                        <!--begin::Menu 1-->
                        <div class="menu menu-sub menu-sub-dropdown w-300px w-md-325px" data-kt-menu="true"
                            id="kt-toolbar-filter">
                            <div class="px-7 py-5">
                                <div class="fs-4 text-dark fw-bolder">Filter Options</div>
                            </div>
                            <div class="separator border-gray-200"></div>
                            <div class="px-7 py-5">
                                @if (auth()->user()->warehouse_id == null)
                                    <div class="mb-10">
                                        <label class="form-label fs-5 fw-bold mb-3">Gudang Asal:</label>
                                        <select class="form-select form-select-solid fw-bolder" data-kt-select2="true"
                                            id="from_warehouse_filter" data-dropdown-parent="#kt-toolbar-filter">
                                            <option value="semua">Semua</option>
                                            @foreach ($warehouses as $warehouse)
                                                <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif
                                <div class="mb-10">
                                    <label class="form-label fs-5 fw-bold mb-3">Gudang Tujuan:</label>
                                    <select class="form-select form-select-solid fw-bolder" data-kt-select2="true"
                                        id="to_warehouse_filter" data-dropdown-parent="#kt-toolbar-filter">
                                        <option value="semua">Semua</option>
                                        @foreach ($warehouses as $warehouse)
                                            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-10">
                                    <label class="form-label fs-5 fw-bold mb-3">Status:</label>
                                    <select class="form-select form-select-solid fw-bolder" data-kt-select2="true"
                                        id="status_filter" data-dropdown-parent="#kt-toolbar-filter">
                                        <option value="semua">Semua</option>
                                        <option value="pending">Pending</option>
                                        <option value="approved">Disetujui</option>
                                        <option value="shipped">Dikirim</option>
                                        <option value="completed">Selesai</option>
                                        <option value="rejected">Ditolak</option>
                                    </select>
                                </div>
                                <div class="mb-10">
                                    <label class="form-label fs-5 fw-bold mb-3">Tanggal:</label>
                                    <select class="form-select form-select-solid fw-bolder" data-kt-select2="true"
                                        id="date_filter_options" data-dropdown-parent="#kt-toolbar-filter">
                                        <option value="semua">Semua</option>
                                        <option value="pilih_tanggal">Pilih Tanggal</option>
                                    </select>
                                    <input class="form-control form-control-solid mt-3" placeholder="Pilih Tanggal"
                                        id="date_filter" style="display: none;" />
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
            </div>
            <div class="card-body pt-4">
                <div class="text-center mb-5">
                    <h3 class="mb-0">Daftar Permintaan Transfer Gudang Masuk</h3>
                    <small id="filter-info" class="text-muted"></small>
                </div>
                <div class="dataTables_wrapper dt-bootstrap4 no-footer">
                    <div class="table-responsive min-h-500px">
                        <table class="table align-middle table-row-dashed fs-6 gy-5 dataTable no-footer" id="table-on-page">
                            <thead>
                                <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                                    <th class="min-w-125px sorting">Kode</th>
                                    <th class="min-w-125px sorting">Tanggal</th>
                                    <th class="min-w-125px sorting">Gudang Asal</th>
                                    <th class="min-w-125px sorting">Gudang Tujuan</th>
                                    <th class="min-w-125px sorting">Status</th>
                                    <th class="min-w-125px sorting">Dibuat Oleh</th>
                                    <th class="text-center min-w-125px sorting_disabled">Actions</th>
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

    <div class="modal fade" tabindex="-1" id="kt_modal_kirim_barang">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Form Pengiriman Barang</h5>
                    <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close">
                        <span class="svg-icon svg-icon-2x"></span>
                    </div>
                </div>
                <form id="kirim_barang_form" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="shipping_date" class="form-label required">Tanggal Kirim</label>
                            <input type="text" class="form-control" id="shipping_date" name="shipping_date" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="vehicle_type" class="form-label required">Tipe Kendaraan</label>
                                <input type="text" class="form-control" id="vehicle_type" name="vehicle_type" required>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="license_plate" class="form-label required">Plat Nomor</label>
                                <input type="text" class="form-control" id="license_plate" name="license_plate" required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="driver_name" class="form-label">Nama Pengemudi</label>
                                <input type="text" class="form-control" id="driver_name" name="driver_name">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="driver_contact" class="form-label">Kontak Pengemudi</label>
                                <input type="text" class="form-control" id="driver_contact" name="driver_contact">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Deskripsi / Catatan</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Kirim Barang</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        var table; // Declare table globally
        $(document).ready(function() {
            $('#date_filter').flatpickr({
                defaultDate: new Date(),
                onChange: function(selectedDates, dateStr, instance) {
                    if ($('#date_filter_options').val() !== 'pilih_tanggal') {
                        $('#date_filter_options').val('pilih_tanggal').trigger('change');
                    }
                }
            });

            $('#date_filter_options').on('change', function() {
                if ($(this).val() === 'pilih_tanggal') {
                    $('#date_filter').show();
                } else {
                    $('#date_filter').hide();
                    $('#date_filter').val(''); // Clear the date when 'Semua' is selected
                }
            });

            $('#shipping_date').flatpickr({
                enableTime: false,
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

            @if (Session::has('success'))
                toastr.success("{{ session('success') }}");
            @endif

            @if (Session::has('error'))
                toastr.error("{{ session('error') }}");
            @endif

            function loadDataTable() {
                var toWarehouseFilter = $('#to_warehouse_filter').val();
                var fromWarehouseFilter = $('#from_warehouse_filter').val();
                var statusFilter = $('#status_filter').val();
                var dateFilter = $('#date_filter_options').val() === 'semua' ? 'semua' : $('#date_filter').val();

                var toWarehouseText = $('#to_warehouse_filter option:selected').text();
                var fromWarehouseText = $('#from_warehouse_filter option:selected').text();
                var statusText = $('#status_filter option:selected').text();
                var dateText = dateFilter === 'semua' ? 'Semua Tanggal' : dateFilter;

                let filterInfoText = `Tanggal: ${dateText} | Gudang Tujuan: ${toWarehouseText} | Status: ${statusText}`;

                if ("{{ auth()->user()->warehouse_id == null }}") {
                    filterInfoText += ` | Gudang Asal: ${fromWarehouseText}`;
                }

                $('#filter-info').text(filterInfoText);

                if ($.fn.DataTable.isDataTable('#table-on-page')) {
                    $('#table-on-page').DataTable().destroy();
                }

                table = $('#table-on-page').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: "{{ route('admin.transfergudang.permintaan-masuk.index') }}",
                        type: "GET",
                        data: function(d) {
                            d.search.value = $('#search_input').val();
                            d.to_warehouse_id = toWarehouseFilter;
                            d.from_warehouse_id = fromWarehouseFilter;
                            d.status = statusFilter;
                            d.date = dateFilter;
                        }
                    },
                    drawCallback: function(settings) {
                        KTMenu.createInstances();
                    },
                    columns: [{
                            data: 'code',
                            name: 'code'
                        },
                        {
                            data: 'date',
                            name: 'date'
                        },
                        {
                            data: 'from_warehouse_name',
                            name: 'fromWarehouse.name'
                        },
                        {
                            data: 'to_warehouse_name',
                            name: 'toWarehouse.name'
                        },
                        {
                            data: 'status',
                            name: 'status'
                        },
                        {
                            data: 'requester_name',
                            name: 'requester.name'
                        },
                        {
                            data: 'id',
                            name: 'id',
                            orderable: false,
                            searchable: false
                        },
                    ],
                    order: [
                        [1, 'desc']
                    ], // Default order by date descending
                    columnDefs: [{
                            targets: 1, // Date column
                            render: function(data, type, row) {
                                const d = new Date(data);
                                const day = ('0' + d.getDate()).slice(-2);
                                const month = d.toLocaleString('en-GB', {
                                    month: 'short'
                                });
                                const year = d.getFullYear();
                                return `${day} ${month} ${year}`;
                            }
                        },
                        {
                            targets: 4, // Status column
                            render: function(data, type, row) {
                                let badgeClass = 'primary';
                                if (data === 'completed') {
                                    badgeClass = 'success';
                                } else if (data === 'rejected') {
                                    badgeClass = 'danger';
                                } else if (data === 'shipped') {
                                    badgeClass = 'warning';
                                } else if (data === 'approved') {
                                    badgeClass = 'info';
                                }
                                return `<span class="badge badge-light-${badgeClass}">${data}</span>`;
                            }
                        },
                        {
                            targets: 6, // Actions column
                            render: function(data, type, row) {
                                let showUrl =
                                    "{{ route('admin.transfergudang.permintaan-masuk.show', ':id') }}"
                                    .replace(':id', row.id);

                                let actionsHtml = `
                                <a href="#" class="btn btn-sm btn-light btn-active-light-primary" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">Actions
                                    <span class="svg-icon svg-icon-5 m-0">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                            <path d="M11.4343 12.7344L7.25 8.55005C6.83579 8.13583 6.16421 8.13584 5.75 8.55005C5.33579 8.96426 5.33579 9.63583 5.75 10.05L11.2929 15.5929C11.6834 15.9835 12.3166 15.9835 12.7071 15.5929L18.25 10.05C18.6642 9.63584 18.6642 8.96426 18.25 8.55005C17.8358 8.13584 17.1642 8.13584 16.75 8.55005L12.5657 12.7344C12.2533 13.0468 11.7467 13.0468 11.4343 12.7344Z" fill="black"></path>
                                        </svg>
                                    </span>
                                </a>
                                <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-bold fs-7 w-125px py-4" data-kt-menu="true">
                                    <div class="menu-item px-3">
                                        <a href="${showUrl}" class="menu-link px-3">Detail</a>
                                    </div>`;

                                if (row.status === 'pending') {
                                    actionsHtml += `
                                    <div class="menu-item px-3">
                                        <a href="#" class="menu-link px-3" onclick="confirmStatusChange(${row.id}, 'approved', '${row.code}')">Approve</a>
                                    </div>`;
                                } else if (row.status === 'approved') {
                                    actionsHtml += `
                                    <div class="menu-item px-3">
                                        <a href="#" class="menu-link px-3 kirim-barang-btn" data-bs-toggle="modal" data-bs-target="#kt_modal_kirim_barang" data-id="${row.id}" data-code="${row.code}">Kirim Barang</a>
                                    </div>`;
                                }

                                actionsHtml += `</div>`;
                                return actionsHtml;
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

            // --- Debounce function start ---
            const debounce = (callback, wait) => {
                let timeoutId = null;
                return (...args) => {
                    window.clearTimeout(timeoutId);
                    timeoutId = window.setTimeout(() => {
                        callback.apply(null, args);
                    }, wait);
                };
            }
            // --- Debounce function end ---

            // Re-draw table on search input change with debounce
            $('#search_input').on('keyup', debounce(function() {
                table.draw();
            }, 500)); // 500ms delay

            window.confirmStatusChange = function(id, status, code) {
                let confirmationText = "";
                let successText = "";
                if (status === 'approved') {
                    confirmationText = `Apakah Anda yakin ingin menyetujui permintaan transfer ${code}?`;
                    successText = `Permintaan transfer ${code} berhasil disetujui.`;
                }

                Swal.fire({
                    text: confirmationText,
                    icon: "warning",
                    showCancelButton: true,
                    buttonsStyling: false,
                    confirmButtonText: "Ya, lanjutkan!",
                    cancelButtonText: "Tidak, batalkan",
                    customClass: {
                        confirmButton: "btn fw-bold btn-success",
                        cancelButton: "btn fw-bold btn-active-light-light"
                    }
                }).then(function(result) {
                    if (result.value) {
                        $.ajax({
                            url: `/admin/transfer-gudang/permintaan-masuk/${id}/update-status`,
                            type: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                status: status
                            },
                            success: function(response) {
                                toastr.success(successText);
                                table.ajax.reload(null, false);
                            },
                            error: function(xhr) {
                                toastr.error("Gagal mengubah status. Silakan coba lagi.");
                            }
                        });
                    } else if (result.dismiss === 'cancel') {
                        toastr.info("Perubahan status dibatalkan.");
                    }
                });
            };

            const kirimBarangModal = document.getElementById('kt_modal_kirim_barang');
            const form = kirimBarangModal.querySelector('#kirim_barang_form');

            kirimBarangModal.addEventListener('show.bs.modal', event => {
                const button = event.relatedTarget;
                const transferId = button.getAttribute('data-id');
                const transferCode = button.getAttribute('data-code');

                const modalTitle = kirimBarangModal.querySelector('.modal-title');
                modalTitle.textContent = 'Form Pengiriman Barang: ' + transferCode;

                let action = `/admin/transfer-gudang/permintaan-masuk/${transferId}/create-shipment`;
                form.action = action;
            });

            $(form).on('submit', function(e) {
                e.preventDefault();
                $('.is-invalid').removeClass('is-invalid');
                $('.invalid-feedback').text('');

                Swal.fire({
                    text: "Apakah Anda yakin ingin menyimpan data pengiriman ini?",
                    icon: "question",
                    showCancelButton: true,
                    buttonsStyling: false,
                    confirmButtonText: "Ya, Simpan!",
                    cancelButtonText: "Tidak, Batalkan",
                    customClass: {
                        confirmButton: "btn fw-bold btn-primary",
                        cancelButton: "btn fw-bold btn-active-light-primary"
                    }
                }).then(function(result) {
                    if (result.value) {
                        let formData = new FormData(form);
                        $.ajax({
                            url: $(form).attr('action'),
                            type: 'POST',
                            data: formData,
                            processData: false,
                            contentType: false,
                            success: function(response) {
                                $(kirimBarangModal).modal('hide');
                                toastr.success(`Data pengiriman berhasil disimpan.`);
                                table.ajax.reload(null, false);
                                form.reset();
                            },
                            error: function(xhr) {
                                if (xhr.status === 422) {
                                    var errors = xhr.responseJSON.errors;
                                    $.each(errors, function(key, value) {
                                        let field = $('[name="' + key + '"]');
                                        field.addClass('is-invalid');
                                        field.next('.invalid-feedback').text(value[0]);
                                    });
                                    toastr.error('Silakan perbaiki error validasi yang ada.','Validasi Gagal');
                                } else {
                                    toastr.error("Gagal menyimpan data pengiriman. Silakan coba lagi.");
                                }
                            }
                        });
                    }
                });
            });

        });
    </script>
@endpush
