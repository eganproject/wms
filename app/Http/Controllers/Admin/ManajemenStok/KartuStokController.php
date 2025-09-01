<?php

namespace App\Http\Controllers\Admin\ManajemenStok;

use App\Http\Controllers\Controller;
use App\Models\StockMovement;
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

            $columns = [
                0 => 'date',
                1 => 'reference',
                2 => 'stock_in',
                3 => 'stock_out',
                4 => 'balance',
            ];

            $orderByColumnIndex = $request->input('order.0.column', 0);
            $orderByColumnName = $columns[$orderByColumnIndex] ?? $columns[0];
            $orderDirection = $request->input('order.0.dir', 'asc');

            $query = StockMovement::query()->select([
                'stock_movements.date',
                DB::raw('CASE 
                    WHEN stock_movements.reference_type = \'stock_in_order_items\' THEN sio.code
                    ELSE stock_movements.description
                END as reference'),
                DB::raw('CASE WHEN stock_movements.type = \'stock_in\' THEN stock_movements.quantity ELSE 0 END as stock_in'),
                DB::raw('CASE WHEN stock_movements.type = \'stock_out\' THEN stock_movements.quantity ELSE 0 END as stock_out'),
                DB::raw('(SELECT SUM(CASE WHEN sm2.type = \'stock_in\' THEN sm2.quantity WHEN sm2.type = \'stock_out\' THEN -sm2.quantity ELSE 0 END) FROM stock_movements sm2 WHERE sm2.id <= stock_movements.id) as balance')
            ])
            ->leftJoin('stock_in_order_items as si_items', 'stock_movements.reference_id', '=', 'si_items.id')
            ->leftJoin('stock_in_orders as sio', 'si_items.stock_in_order_id', '=', 'sio.id');

            $totalRecords = $query->count();

            if (!empty($searchValue)) {
                $query->where(function ($q) use ($searchValue) {
                    $q->where('sio.code', 'LIKE', "%{$searchValue}%")
                      ->orWhere('stock_movements.description', 'LIKE', "%{$searchValue}%");
                });
            }

            if ($dateFilter) {
                $dates = explode(' to ', $dateFilter);
                if (count($dates) == 2) {
                    $query->whereBetween('stock_movements.date', [$dates[0], $dates[1]]);
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

        return view('admin.manajemenstok.kartustok.index');
    }
}