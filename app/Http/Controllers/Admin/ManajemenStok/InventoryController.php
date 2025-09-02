<?php

namespace App\Http\Controllers\Admin\ManajemenStok;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\ItemCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $searchValue = $request->input('search.value', '');
            $start = $request->input('start', 0);
            $length = $request->input('length', 10);
            $draw = $request->input('draw', 0);
            $itemCategoryFilter = $request->input('item_category_filter');

            $columns = [
                0 => 'items.sku',
                1 => 'items.nama_barang',
                2 => 'total_quantity',
                3 => 'total_koli',
            ];

            $orderByColumnIndex = $request->input('order.0.column', 0);
            $orderByColumnName = $columns[$orderByColumnIndex] ?? $columns[0];
            $orderDirection = $request->input('order.0.dir', 'asc');

            $query = Inventory::query()
                ->join('items', 'inventories.item_id', '=', 'items.id')
                ->select([
                    'items.sku as sku',
                    'items.nama_barang as item_name',
                    DB::raw('SUM(inventories.quantity) as total_quantity'),
                    DB::raw('SUM(inventories.koli) as total_koli')
                ])
                ->groupBy('items.id', 'items.sku', 'items.nama_barang');

            if ($itemCategoryFilter) {
                $query->where('items.item_category_id', $itemCategoryFilter);
            }

            $totalRecords = $query->get()->count();

            if (!empty($searchValue)) {
                $query->where(function ($q) use ($searchValue) {
                    $q->where('items.nama_barang', 'LIKE', "%{$searchValue}%")
                      ->orWhere('items.sku', 'LIKE', "%{$searchValue}%");
                });
            }

            $totalFiltered = $query->get()->count();

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

        $itemCategories = ItemCategory::all();
        return view('admin.manajemenstok.masterstok.index', compact('itemCategories'));
    }
}
