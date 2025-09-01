<?php

namespace App\Http\Controllers\Admin\ManajemenStok;

use App\Http\Controllers\Controller;
use App\Models\StockMovement;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KartuStokController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $searchValue = $request->input('search.value', '');
            $start = $request->input('start', 0);
            $length = $request->input('length', 10);
            $draw = $request->input('draw', 0);
            $dateFilter = $request->input('date_filter');
            $warehouseFilter = $request->input('warehouse_filter');

            $columns = [
                0 => 'date',
                1 => 'reference',
                2 => 'sku_name',
                3 => 'warehouse_name',
                4 => 'stock_in',
                5 => 'stock_out',
                6 => 'balance',
            ];

            $orderByColumnIndex = $request->input('order.0.column', 0);
            $orderByColumnName = $columns[$orderByColumnIndex] ?? $columns[0];
            $orderDirection = $request->input('order.0.dir', 'asc');

            $query = StockMovement::query()->from('stock_movements as sm')->select([
                'sm.date',
                'i.sku as sku_name',
                'w.name as warehouse_name',
                DB::raw('CASE 
                    WHEN sm.reference_type = \'stock_in_order_items\' THEN sio.code
                    ELSE sm.description
                END as reference'),
                DB::raw('CASE WHEN sm.type = \'stock_in\' THEN sm.quantity ELSE 0 END as stock_in'),
                DB::raw('CASE WHEN sm.type = \'stock_out\' THEN sm.quantity ELSE 0 END as stock_out'),
                DB::raw('(SELECT SUM(CASE WHEN sm2.type = \'stock_in\' THEN sm2.quantity WHEN sm2.type = \'stock_out\' THEN -sm2.quantity ELSE 0 END) FROM stock_movements sm2 WHERE sm2.item_id = sm.item_id AND sm2.id <= sm.id AND sm2.warehouse_id = sm.warehouse_id) as balance')
            ])
            ->leftJoin('items as i', 'sm.item_id', '=', 'i.id')
            ->leftJoin('stock_in_order_items as si_items', 'sm.reference_id', '=', 'si_items.id')
            ->leftJoin('stock_in_orders as sio', 'si_items.stock_in_order_id', '=', 'sio.id')
            ->leftJoin('warehouses as w', 'sm.warehouse_id', '=', 'w.id');

            if ($warehouseFilter) {
                $query->where('sm.warehouse_id', $warehouseFilter);
            }

            $totalRecords = $query->count();

            if (!empty($searchValue)) {
                $query->where(function ($q) use ($searchValue) {
                    $q->where('sio.code', 'LIKE', "%{$searchValue}%")
                      ->orWhere('sm.description', 'LIKE', "%{$searchValue}%")
                      ->orWhere('w.name', 'LIKE', "%{$searchValue}%")
                      ->orWhere('i.sku', 'LIKE', "%{$searchValue}%");
                });
            }

            if ($dateFilter) {
                $dates = explode(' to ', $dateFilter);
                if (count($dates) == 2) {
                    $query->whereBetween('sm.date', [$dates[0], $dates[1]]);
                }
            }

            $totalFiltered = $query->count();

            $data = $query->orderBy($orderByColumnName, $orderDirection)
                ->offset($start)
                ->limit($length)
                ->get();

            return response()->json([
                'draw' => intval($draw),
                'recordsTotal' => intval($totalRecords),
                'recordsFiltered' => intval($totalFiltered),
                'data' => $data,
            ]);
        }

        $warehouses = Warehouse::all();
        return view('admin.manajemenstok.kartustok.index', compact('warehouses'));
    }
}
