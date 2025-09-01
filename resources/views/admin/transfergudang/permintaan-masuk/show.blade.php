@extends('layouts.app')

@push('styles')
    <style>
        @media print {

            body * {
                visibility: hidden;
            }

            #kt_content,
            #kt_content * {
                visibility: visible;
            }

            #kt_content {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }

            .card-body {
                padding: 0 !important;
            }

            .d-flex.flex-stack.pb-10,
            #print_button {
                display: none !important;
            }
        }
    </style>
@endpush

@push('toolbar')
    @include('layouts.partials._toolbar', [
        'title' => 'Detail Permintaan Transfer',
        'breadcrumbs' => ['Admin', 'Transfer Gudang', 'Permintaan Masuk', 'Detail'],
    ])
@endpush

@section('content')
    <div class="content flex-row-fluid" id="kt_content">
        @php
            $status = $transferRequest->status;
            $ribbonBg = 'primary';
            if ($status === 'completed') {
                $ribbonBg = 'success';
            } elseif ($status === 'rejected') {
                $ribbonBg = 'danger';
            } elseif ($status === 'shipped') {
                $ribbonBg = 'warning';
            }
        @endphp
        <div class="card ribbon ribbon-end ribbon-clip">
            <div class="ribbon-label d-print-none">
                {{ ucfirst($status) }}
                <span class="ribbon-inner bg-{{ $ribbonBg }}"></span>
            </div>
            <div class="card-body p-lg-20">
                <!--begin::Layout-->
                <div class="d-flex flex-column flex-xl-row">
                    <!--begin::Content-->
                    <div class="flex-lg-row-fluid me-xl-18 mb-10 mb-xl-0">
                        <!--begin::Invoice 2 content-->
                        <div class="mt-n1">
                            <!--begin::Top-->
                            <div class="d-flex flex-stack pb-10">
                                <!--begin::Logo-->
                                <a href="#">
                                    <img alt="Logo"
                                        src="{{ asset('metronic/assets/media/svg/brand-logos/code-lab.svg') }}" />
                                </a>
                                <!--end::Logo-->
                            </div>
                            <!--end::Top-->
                            <!--begin::Wrapper-->
                            <div class="m-0">
                                <!--begin::Label-->
                                <div class="fw-bolder fs-3 text-gray-800 mb-8">Permintaan Transfer
                                    #{{ $transferRequest->code }}
                                </div>
                                <!--end::Label-->
                                <!--begin::Row-->
                                <div class="row g-5 mb-11">
                                    <!--end::Col-->
                                    <div class="col-sm-6">
                                        <!--end::Label-->
                                        <div class="fw-bold fs-7 text-gray-600 mb-1">Tanggal Dibuat:</div>
                                        <!--end::Label-->
                                        <!--end::Col-->
                                        <div class="fw-bolder fs-6 text-gray-800">
                                            {{ \Carbon\Carbon::parse($transferRequest->date)->format('d M Y') }}</div>
                                        <!--end::Col-->
                                    </div>
                                    <!--end::Col-->
                                    <!--end::Col-->
                                    <div class="col-sm-6">
                                        <!--end::Label-->
                                        <div class="fw-bold fs-7 text-gray-600 mb-1">Gudang Asal:</div>
                                        <!--end::Label-->
                                        <!--end::Info-->
                                        <div class="fw-bolder fs-6 text-gray-800 d-flex align-items-center flex-wrap">
                                            <span class="pe-2">{{ $transferRequest->fromWarehouse->name }}</span>
                                        </div>
                                        <!--end::Info-->
                                    </div>
                                    <!--end::Col-->
                                </div>
                                <!--end::Row-->
                                <!--begin::Row-->
                                <div class="row g-5 mb-12">
                                    <!--end::Col-->
                                    <div class="col-sm-6">
                                        <!--end::Label-->
                                        <div class="fw-bold fs-7 text-gray-600 mb-1">Dibuat Oleh:</div>
                                        <!--end::Label-->
                                        <!--end::Text-->
                                        <div class="fw-bolder fs-6 text-gray-800">{{ $transferRequest->requester->name }}
                                        </div>
                                        <!--end::Text-->
                                        <!--end::Description-->
                                        <div class="fw-bold fs-7 text-gray-600">
                                            {{ $transferRequest->requester->jabatan->name ?? '' }}</div>
                                        <!--end::Description-->
                                    </div>
                                    <div class="col-sm-6">
                                        <!--end::Label-->
                                        <div class="mb-12">
                                            <div class="fw-bold fs-7 text-gray-600 mb-1">Gudang Tujuan:</div>
                                            <!--end::Label-->
                                            <!--end::Info-->
                                            <div class="fw-bolder fs-6 text-gray-800 d-flex align-items-center flex-wrap">
                                                <span class="pe-2">{{ $transferRequest->toWarehouse->name }}</span>
                                            </div>
                                        </div>
                                        <!--end::Info-->
                                        <div class="mb-12">
                                            <div class="fw-bold fs-7 text-gray-600 mb-1">Status:</div>
                                        <!--end::Label-->
                                        <div class="fw-bolder fs-6 text-gray-800">
                                            @php
                                                $badgeClass = 'primary';
                                                if ($transferRequest->status === 'completed') {
                                                    $badgeClass = 'success';
                                                } elseif ($transferRequest->status === 'rejected') {
                                                    $badgeClass = 'danger';
                                                }
                                            @endphp
                                            <span
                                                class="badge badge-light-{{ $badgeClass }}">{{ $transferRequest->status }}</span>
                                        </div>
                                        </div>
                                    </div>
                                    <!--end::Col-->
                                </div>
                                <!--end::Row-->
                                <!--begin::Table-->
                                @php
                                    $totalQuantity = $transferRequest->items->sum('quantity');
                                    $totalKoli = $transferRequest->items->sum('koli');
                                @endphp
                                <div class="table-responsive mb-10">
                                    <table class="table g-5 gs-0 mb-0 fw-bolder text-gray-700">
                                        <!--begin::Table head-->
                                        <thead>
                                            <tr class="border-bottom fs-7 fw-bolder text-gray-700 text-uppercase">
                                                <th class="min-w-300px w-475px">Item</th>
                                                <th class="min-w-100px w-100px">SKU</th>
                                                <th class="min-w-100px w-150px text-end">Kuantitas</th>
                                                <th class="min-w-100px w-150px text-end">Koli</th>
                                            </tr>
                                        </thead>
                                        <!--end::Table head-->
                                        <!--begin::Table body-->
                                        <tbody>
                                            @foreach ($transferRequest->items as $item)
                                                <tr class="border-bottom border-bottom-dashed">
                                                    <td class="pe-7">
                                                        <div class="d-flex align-items-center">
                                                            <div class="d-flex flex-column">
                                                                <a href="#"
                                                                    class="text-gray-800 text-hover-primary fs-6 fw-bolder">{{ $item->item->nama_barang }}</a>
                                                                <span
                                                                    class="text-gray-600 fw-bold">{{ $item->item->description }}</span>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="pe-7">{{ $item->item->sku }}</td>
                                                    <td class="text-end">{{ $item->quantity }}</td>
                                                    <td class="text-end">{{ $item->koli }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <!--end::Table body-->
                                        <!--begin::Table foot-->
                                        <tfoot>
                                            <tr class="border-top-2 border-top-dashed fs-6 fw-bolder text-gray-700">
                                                <th colspan="2" class="text-end">Total</th>
                                                <th class="text-end">{{ $totalQuantity }}</th>
                                                <th class="text-end">{{ $totalKoli }}</th>
                                            </tr>
                                        </tfoot>
                                        <!--end::Table foot-->
                                    </table>
                                </div>
                                <!--end::Table-->
                                <!--begin::Notes-->
                                <div class="mb-0">
                                    <div class="fw-bolder fs-7 text-gray-600 mb-1">Catatan:</div>
                                    <div class="fw-bold fs-6 text-gray-800">{{ $transferRequest->description ?? '-' }}
                                    </div>
                                </div>
                                <!--end::Notes-->
                            </div>
                            <!--end::Wrapper-->
                        </div>
                        <div class="d-flex flex-stack flex-wrap mt-lg-20 pt-13 d-print-none">
                            <!-- begin::Actions-->
                            <div class="my-1 me-5">
                                <!-- begin::Pint-->
                                <a href="{{ route('admin.transfergudang.permintaan-masuk.index') }}"
                                    class="btn btn-secondary my-1 me-12">Kembali</a>

                                <!-- end::Pint-->
                            </div>
                            <!-- end::Actions-->
                            <!-- begin::Action-->
                            <button type="button" class="btn btn-success my-1 me-12" id="print_button">Print</button>
                            <!-- end::Action-->
                        </div>
                        <!--end::Invoice 2 content-->
                    </div>
                    <!--end::Content-->
                </div>
                <!--end::Layout-->
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $('#print_button').click(function() {
            window.print();
        });
    </script>
@endpush