@extends('layouts.app')

@push('toolbar')
    @include('layouts.partials._toolbar', [
        'title' => 'Permintaan Transfer Gudang',
        'breadcrumbs' => ['Admin', 'Transfer Gudang', 'Buat Permintaan Transfer'],
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
                        <input type="text" id="search_input" class="form-control form-control-solid w-250px ps-15" placeholder="Cari Permintaan Transfer">
                    </div>
                </div>
                <div class="card-toolbar">
                    <div class="d-flex justify-content-end">
                        <a href="{{ route('admin.transfergudang.buat-permintaan-transfer.create') }}" class="btn btn-primary">Buat Permintaan</a>
                    </div>
                </div>
            </div>
            <div class="card-body pt-4">
                <div class="table-responsive">
                    <table class="table align-middle table-row-dashed fs-6 gy-5" id="transfer_requests_table">
                        <thead>
                            <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                                <th>Kode</th>
                                <th>Tanggal</th>
                                <th>Gudang Asal</th>
                                <th>Gudang Tujuan</th>
                                <th>Status</th>
                                <th>Dibuat Oleh</th>
                                <th class="text-end min-w-70px">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="fw-bold text-gray-600">
                            @foreach ($transferRequests as $request)
                                <tr>
                                    <td>{{ $request->code }}</td>
                                    <td>{{ \Carbon\Carbon::parse($request->date)->format('d M Y') }}</td>
                                    <td>{{ $request->fromWarehouse->name }}</td>
                                    <td>{{ $request->toWarehouse->name }}</td>
                                    <td>
                                        @php
                                            $badgeClass = 'primary';
                                            if ($request->status === 'completed') $badgeClass = 'success';
                                            elseif ($request->status === 'rejected') $badgeClass = 'danger';
                                            elseif ($request->status === 'shipped') $badgeClass = 'warning';
                                            elseif ($request->status === 'approved') $badgeClass = 'info';
                                        @endphp
                                        <span class="badge badge-light-{{ $badgeClass }}">{{ ucfirst($request->status) }}</span>
                                    </td>
                                    <td>{{ $request->requester->name ?? 'N/A' }}</td>
                                    <td class="text-end">
                                        <a href="#" class="btn btn-sm btn-light btn-active-light-primary" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">Actions
                                            <span class="svg-icon svg-icon-5 m-0">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                                    <path d="M11.4343 12.7344L7.25 8.55005C6.83579 8.13583 6.16421 8.13584 5.75 8.55005C5.33579 8.96426 5.33579 9.63583 5.75 10.05L11.2929 15.5929C11.6834 15.9835 12.3166 15.9835 12.7071 15.5929L18.25 10.05C18.6642 9.63584 18.6642 8.96426 18.25 8.55005C17.8358 8.13584 17.1642 8.13584 16.75 8.55005L12.5657 12.7344C12.2533 13.0468 11.7467 13.0468 11.4343 12.7344Z" fill="black" />
                                                </svg>
                                            </span>
                                        </a>
                                        <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-bold fs-7 w-125px py-4" data-kt-menu="true">
                                            <div class="menu-item px-3">
                                                <a href="{{ route('admin.transfergudang.buat-permintaan-transfer.show', $request->id) }}" class="menu-link px-3">View</a>
                                            </div>
                                            @if ($request->status == 'pending')
                                            <div class="menu-item px-3">
                                                <a href="{{ route('admin.transfergudang.buat-permintaan-transfer.edit', $request->id) }}" class="menu-link px-3">Edit</a>
                                            </div>
                                            <div class="menu-item px-3">
                                                <form class="form-delete" action="{{ route('admin.transfergudang.buat-permintaan-transfer.destroy', $request->id) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="menu-link px-3 border-0 bg-transparent w-100 text-start" data-document-code="{{ $request->code }}">Delete</button>
                                                </form>
                                            </div>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    {{ $transferRequests->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
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

            // Delete confirmation
            $('.form-delete').on('submit', function(e) {
                e.preventDefault();
                var form = $(this);
                var code = form.find('[data-document-code]').data('document-code');

                Swal.fire({
                    text: "Apakah Anda yakin ingin menghapus permintaan " + code + "?",
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
                        form.off('submit').submit();
                    } else if (result.dismiss === 'cancel') {
                        toastr.info("Penghapusan permintaan " + code + " dibatalkan.");
                    }
                });
            });
        });
    </script>
@endpush
