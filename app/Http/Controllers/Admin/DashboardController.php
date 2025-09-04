<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\StockInOrder;
use App\Models\StockOut;
use App\Models\TransferRequest;
use App\Models\User;
use App\Models\UserActivity;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $warehouseId = $user->warehouse_id;

        if ($warehouseId) {
            // Data for users with a warehouse
            $recentActivities = UserActivity::where('user_id', $user->id)
                ->latest()
                ->take(5)
                ->get();

            $todayStockIn = StockInOrder::where('warehouse_id', $warehouseId)
                ->whereDate('date', today())
                ->count();

            $todayStockOut = StockOut::where('warehouse_id', $warehouseId)
                ->whereDate('date', today())
                ->count();

            $pendingTransfers = TransferRequest::where('to_warehouse_id', $warehouseId)
                ->where('status', 'shipped')
                ->count();

            // TODO: Make the low stock threshold configurable
            $lowStockItems = Inventory::where('warehouse_id', $warehouseId)
                ->where('quantity', '<=', 10)
                ->count();

            $stockChartData = $this->getStockChartData($warehouseId);

            return view('admin.dashboard.index', compact(
                'user',
                'recentActivities',
                'todayStockIn',
                'todayStockOut',
                'pendingTransfers',
                'lowStockItems',
                'stockChartData'
            ));
        } else {
            // Data for users without a warehouse (system-wide)
            $totalUsers = User::count();
            $totalWarehouses = Warehouse::count();
            $todayStockIn = StockInOrder::whereDate('date', today())->count();
            $todayStockOut = StockOut::whereDate('date', today())->count();
            $recentActivities = UserActivity::with('user')->latest()->take(5)->get();
            $stockChartData = $this->getStockChartData(null);

            return view('admin.dashboard.index', compact(
                'user',
                'totalUsers',
                'totalWarehouses',
                'todayStockIn',
                'todayStockOut',
                'recentActivities',
                'stockChartData'
            ));
        }
    }

    private function getStockChartData($warehouseId)
    {
        $dates = collect();
        for ($i = 6; $i >= 0; $i--) {
            $dates->push(today()->subDays($i)->toDateString());
        }

        $stockInQuery = StockInOrder::where('status', 'completed')
            ->whereBetween('completed_at', [today()->subDays(6), today()->endOfDay()]);

        $stockOutQuery = StockOut::whereBetween('date', [today()->subDays(6), today()->endOfDay()]);

        if ($warehouseId) {
            $stockInQuery->where('warehouse_id', $warehouseId);
            $stockOutQuery->where('warehouse_id', $warehouseId);
        }

        $stockIn = $stockInQuery->select(DB::raw('DATE(completed_at) as date'), DB::raw('count(*) as count'))
            ->groupBy(DB::raw('DATE(completed_at)'))
            ->pluck('count', 'date');

        $stockOut = $stockOutQuery->select(DB::raw('DATE(date) as date'), DB::raw('count(*) as count'))
            ->groupBy(DB::raw('DATE(date)'))
            ->pluck('count', 'date');

        $chartData = [
            'labels' => $dates->map(function ($date) {
                return date('d M', strtotime($date));
            }),
            'stock_in' => $dates->map(function ($date) use ($stockIn) {
                return $stockIn->get($date, 0);
            }),
            'stock_out' => $dates->map(function ($date) use ($stockOut) {
                return $stockOut->get($date, 0);
            }),
        ];

        return $chartData;
    }
}