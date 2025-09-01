<?php

namespace App\Http\Controllers\Admin\ManajemenStok;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WarehouseStokController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $searchValue = $request->input('search.value', '');
            $start = $request->input('start', 0);
            $length = $request->input('length', 10);
            $draw = $request->input('draw', 0);
            $warehouseFilter = $request->input('warehouse_filter');

            $columns = [
                0 => 'items.sku',
                1 => 'items.nama_barang',
                2 => 'warehouses.name',
                3 => 'inventories.quantity',
                4 => 'inventories.koli',
            ];

            $orderByColumnIndex = $request->input('order.0.column', 0);
            $orderByColumnName = $columns[$orderByColumnIndex] ?? $columns[0];
            $orderDirection = $request->input('order.0.dir', 'asc');

            $query = Inventory::query()
                ->join('items', 'inventories.item_id', '=', 'items.id')
                ->join('warehouses', 'inventories.warehouse_id', '=', 'warehouses.id')
                ->select([
                    'items.sku as sku',
                    'items.nama_barang as item_name',
                    'warehouses.name as warehouse_name',
                    'inventories.quantity',
                    'inventories.koli'
                ]);

            if (Auth::user()->warehouse_id) {
                $query->where('inventories.warehouse_id', Auth::user()->warehouse_id);
            } elseif ($warehouseFilter) {
                $query->where('inventories.warehouse_id', $warehouseFilter);
            }

            $totalRecords = $query->count();

            if (!empty($searchValue)) {
                $query->where(function ($q) use ($searchValue) {
                    $q->where('items.name', 'LIKE', "%{$searchValue}%")
                      ->orWhere('items.sku', 'LIKE', "%{$searchValue}%")
                      ->orWhere('warehouses.name', 'LIKE', "%{$searchValue}%");
                });
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
        return view('admin.manajemenstok.warehousestok.index', compact('warehouses'));
    }
}