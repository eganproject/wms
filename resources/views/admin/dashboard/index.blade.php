@extends('layouts.app')

@push('styles')
<style>
    .card-animate {
        animation: fadeIn-slideUp 0.5s ease-out forwards;
        opacity: 0;
    }

    @keyframes fadeIn-slideUp {
        0% {
            opacity: 0;
            transform: translateY(20px);
        }
        100% {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .summary-card {
        transition: all 0.3s ease;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }

    .summary-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 20px rgba(0,0,0,0.15);
    }
</style>
@endpush

@section('content')
    <div class="content flex-row-fluid" id="kt_content">
        <!--begin::Row-->
        <div class="row g-5 g-xxl-8">
            <div class="col-12">
                <div class="card bg-light-primary border-0 card-animate">
                    <div class="card-body">
                        <h3 class="card-title">Selamat Datang, {{ $user->name }}!</h3>
                        <p class="text-muted">Berikut adalah ringkasan aktivitas Anda.</p>
                    </div>
                </div>
            </div>
        </div>
        <!--end::Row-->

        <!--begin::Row-->
        <div class="row g-5 g-xxl-8 mt-5">
            @if (isset($totalUsers)) {{-- System-wide view --}}
                <!--begin::Col-->
                <div class="col-xl-3">
                    <div class="card card-xl-stretch mb-xl-8 summary-card card-animate" style="animation-delay: 0.1s;">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="symbol symbol-50px me-5">
                                    <div class="symbol-label bg-light-primary">
                                        <i class="fas fa-users text-primary fs-2x"></i>
                                    </div>
                                </div>
                                <div>
                                    <div class="text-dark fw-bolder fs-2">{{ $totalUsers }}</div>
                                    <div class="text-muted fw-bold">Total Pengguna</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--end::Col-->
                <!--begin::Col-->
                <div class="col-xl-3">
                    <div class="card card-xl-stretch mb-xl-8 summary-card card-animate" style="animation-delay: 0.2s;">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="symbol symbol-50px me-5">
                                    <div class="symbol-label bg-light-warning">
                                        <i class="fas fa-warehouse text-warning fs-2x"></i>
                                    </div>
                                </div>
                                <div>
                                    <div class="text-dark fw-bolder fs-2">{{ $totalWarehouses }}</div>
                                    <div class="text-muted fw-bold">Total Gudang</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--end::Col-->
            @else {{-- Warehouse-specific view --}}
                <!--begin::Col-->
                <div class="col-xl-3">
                    <div class="card card-xl-stretch mb-xl-8 summary-card card-animate" style="animation-delay: 0.1s;">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="symbol symbol-50px me-5">
                                    <div class="symbol-label bg-light-warning">
                                        <i class="fas fa-exchange-alt text-warning fs-2x"></i>
                                    </div>
                                </div>
                                <div>
                                    <div class="text-dark fw-bolder fs-2">{{ $pendingTransfers }}</div>
                                    <div class="text-muted fw-bold">Permintaan Transfer Tertunda</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--end::Col-->
                <!--begin::Col-->
                <div class="col-xl-3">
                    <div class="card card-xl-stretch mb-xl-8 summary-card card-animate" style="animation-delay: 0.2s;">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="symbol symbol-50px me-5">
                                    <div class="symbol-label bg-light-info">
                                        <i class="fas fa-exclamation-triangle text-info fs-2x"></i>
                                    </div>
                                </div>
                                <div>
                                    <div class="text-dark fw-bolder fs-2">{{ $lowStockItems }}</div>
                                    <div class="text-muted fw-bold">Stok Segera Habis</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--end::Col-->
            @endif
            <!--begin::Col-->
            <div class="col-xl-3">
                <div class="card card-xl-stretch mb-xl-8 summary-card card-animate" style="animation-delay: 0.3s;">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="symbol symbol-50px me-5">
                                <div class="symbol-label bg-light-success">
                                    <i class="fas fa-arrow-down text-success fs-2x"></i>
                                </div>
                            </div>
                            <div>
                                <div class="text-dark fw-bolder fs-2">{{ $todayStockIn }}</div>
                                <div class="text-muted fw-bold">Stok Masuk Hari Ini</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--end::Col-->
            <!--begin::Col-->
            <div class="col-xl-3">
                <div class="card card-xl-stretch mb-xl-8 summary-card card-animate" style="animation-delay: 0.4s;">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="symbol symbol-50px me-5">
                                <div class="symbol-label bg-light-danger">
                                    <i class="fas fa-arrow-up text-danger fs-2x"></i>
                                </div>
                            </div>
                            <div>
                                <div class="text-dark fw-bolder fs-2">{{ $todayStockOut }}</div>
                                <div class="text-muted fw-bold">Stok Keluar Hari Ini</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--end::Col-->
        </div>
        <!--end::Row-->

        <!--begin::Row-->
        <div class="row g-5 g-xxl-8">
            <!--begin::Col-->
            <div class="col-xl-6">
                <div class="card card-xl-stretch mb-xl-8 card-animate" style="animation-delay: 0.5s;">
                    <div class="card-header border-0">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bolder text-dark">Aktivitas Terbaru</span>
                            <span class="text-muted mt-1 fw-bold fs-7">5 aktivitas terakhir</span>
                        </h3>
                    </div>
                    <div class="card-body pt-2">
                        @foreach ($recentActivities as $activity)
                            <div class="d-flex align-items-center mb-5">
                                <div class="symbol symbol-35px me-4">
                                    <div class="symbol-label bg-light-primary">
                                        <i class="fas fa-user text-primary"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <p class="text-gray-800 fw-bolder fs-6 mb-0">{{ $activity->description }}</p>
                                    <span class="text-muted fw-bold">
                                        @if (isset($activity->user))
                                            {{ $activity->user->name }} -
                                        @endif
                                        {{ $activity->created_at->diffForHumans() }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <!--end::Col-->
            <!--begin::Col-->
            <div class="col-xl-6">
                <div class="card card-xl-stretch mb-xl-8 card-animate" style="animation-delay: 0.6s;">
                    <div class="card-header border-0">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bolder text-dark">Grafik Stok 7 Hari Terakhir</span>
                            <span class="text-muted mt-1 fw-bold fs-7">Pergerakan stok masuk dan keluar</span>
                        </h3>
                    </div>
                    <div class="card-body">
                        <canvas id="stockChart"></canvas>
                    </div>
                </div>
            </div>
            <!--end::Col-->
        </div>
        <!--end::Row-->
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        $(document).ready(function() {
            var ctx = document.getElementById('stockChart').getContext('2d');

            var gradientIn = ctx.createLinearGradient(0, 0, 0, 400);
            gradientIn.addColorStop(0, 'rgba(75, 192, 192, 0.5)');
            gradientIn.addColorStop(1, 'rgba(75, 192, 192, 0)');

            var gradientOut = ctx.createLinearGradient(0, 0, 0, 400);
            gradientOut.addColorStop(0, 'rgba(255, 99, 132, 0.5)');
            gradientOut.addColorStop(1, 'rgba(255, 99, 132, 0)');

            var stockChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($stockChartData['labels']) !!},
                    datasets: [{
                        label: 'Stok Masuk',
                        data: {!! json_encode($stockChartData['stock_in']) !!},
                        backgroundColor: gradientIn,
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 2,
                        borderRadius: 5,
                        tension: 0.4
                    }, {
                        label: 'Stok Keluar',
                        data: {!! json_encode($stockChartData['stock_out']) !!},
                        backgroundColor: gradientOut,
                        borderColor: 'rgba(255, 99, 132, 1)',
                        borderWidth: 2,
                        borderRadius: 5,
                        tension: 0.4
                    }]
                },
                options: {
                    animation: {
                        duration: 1000,
                        easing: 'easeInOutQuad'
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(200, 200, 200, 0.1)'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.7)',
                            titleFont: {
                                size: 14,
                                weight: 'bold'
                            },
                            bodyFont: {
                                size: 12
                            },
                            callbacks: {
                                label: function(context) {
                                    return `${context.dataset.label}: ${context.formattedValue}`;
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>
@endpush