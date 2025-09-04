<?php

namespace App\Http\Controllers\Admin\StokKeluar;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\Item;
use App\Models\StockOut;
use App\Models\StockOutItem;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PengeluaranBarangController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $searchValue = $request->input('search.value', '');
            $start = $request->input('start', 0);
            $length = $request->input('length', 10);
            $draw = $request->input('draw', 0);
            $warehouseFilter = $request->input('warehouse_id');
            $statusFilter = $request->input('status');
            $dateFilter = $request->input('date');

            $query = StockOut::with(['warehouse', 'user'])
                ->select('stock_outs.* ');

            $totalRecords = $query->count();

            if (!empty($searchValue)) {
                $query->where(function ($q) use ($searchValue) {
                    $q->where('id', 'LIKE', "%{$searchValue}%")
                      ->orWhereHas('warehouse', function ($subQuery) use ($searchValue) {
                          $subQuery->where('name', 'LIKE', "%{$searchValue}%");
                      })
                      ->orWhereHas('user', function ($subQuery) use ($searchValue) {
                          $subQuery->where('name', 'LIKE', "%{$searchValue}%");
                      });
                });
            }
            
            if ($warehouseFilter && $warehouseFilter !== 'semua') {
                $query->where('warehouse_id', $warehouseFilter);
            }

            if ($statusFilter && $statusFilter !== 'semua') {
                $query->where('status', $statusFilter);
            }

            if ($dateFilter) {
                $query->whereDate('stock_out_date', $dateFilter);
            }

            $totalFiltered = $query->count();

            $data = $query->latest()->offset($start)->limit($length)->get();

            return response()->json([
                'draw' => intval($draw),
                'recordsTotal' => intval($totalRecords),
                'recordsFiltered' => intval($totalFiltered),
                'data' => $data,
            ]);
        }

        $warehouses = Warehouse::all();
        return view('admin.stok_keluar.index', compact('warehouses'));
    }

    public function create()
    {
        $warehouses = Warehouse::all();
        $inventory = Inventory::with(['item.uom', 'item'])->where('quantity', '>', 0)->get()->groupBy('warehouse_id');
        return view('admin.stok_keluar.create', compact('warehouses', 'inventory'));
    }

    public function store(Request $request)
    {
        
        $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'stock_out_date' => 'required|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.koli' => 'required|numeric|min:1',
            'items.*.quantity' => [
                'required',
                'numeric',
                'min:1',
                function ($attribute, $value, $fail) use ($request) {
                    $index = explode('.', $attribute)[1];
                    $itemId = $request->input("items.$index.item_id");
                    $warehouseId = $request->input('warehouse_id');

                    $inventory = Inventory::where('warehouse_id', $warehouseId)
                                        ->where('item_id', $itemId)
                                        ->first();

                    if (!$inventory || $inventory->quantity < $value) {
                        $fail('Stok untuk item yang dipilih tidak mencukupi di gudang.');
                    }
                },
            ],
        ]);

        DB::transaction(function () use ($request) {
            $stockOut = StockOut::create([
                'warehouse_id' => $request->warehouse_id,
                'user_id' => auth()->id(),
                'stock_out_date' => $request->stock_out_date,
                'notes' => $request->notes,
                'status' => 'completed',
            ]);

            foreach ($request->items as $itemData) {
                $stockOut->items()->create([
                    'item_id' => $itemData['item_id'],
                    'quantity' => $itemData['quantity'],
                    'koli' => $itemData['koli'],
                ]);

                $inventory = Inventory::where('warehouse_id', $request->warehouse_id)
                                    ->where('item_id', $itemData['item_id'])
                                    ->first();
                $inventory->decrement('quantity', $itemData['quantity']);
            }
        });

        return redirect()->route('admin.stok-keluar.pengeluaran-barang.index')->with('success', 'Data pengeluaran barang berhasil disimpan.');
    }

    public function show(StockOut $pengeluaranBarang)
    {
        $pengeluaranBarang->load(['warehouse', 'user', 'items.item']);
        return view('admin.stok_keluar.show', compact('pengeluaranBarang'));
    }

    public function edit(StockOut $pengeluaranBarang)
    {
        $warehouses = Warehouse::all();
        $inventory = Inventory::with(['item.uom', 'item'])->where('quantity', '>', 0)->get()->groupBy('warehouse_id');
        $pengeluaranBarang->load('items.item');
        return view('admin.stok_keluar.edit', compact('pengeluaranBarang', 'warehouses', 'inventory'));
    }

    public function update(Request $request, StockOut $pengeluaranBarang)
    {
        $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'stock_out_date' => 'required|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.koli' => 'required|numeric|min:1',
            'items.*.quantity' => [
                'required',
                'numeric',
                'min:1',
                function ($attribute, $value, $fail) use ($request, $pengeluaranBarang) {
                    $index = explode('.', $attribute)[1];
                    $itemId = $request->input("items.$index.item_id");
                    
                    $inventory = Inventory::where('warehouse_id', $request->warehouse_id)
                                        ->where('item_id', $itemId)
                                        ->first();

                    $originalQuantity = 0;
                    if ($pengeluaranBarang->warehouse_id == $request->warehouse_id) {
                        $originalItem = $pengeluaranBarang->items()->where('item_id', $itemId)->first();
                        if($originalItem) {
                           $originalQuantity = $originalItem->quantity;
                        }
                    }

                    $currentStock = $inventory ? $inventory->quantity : 0;
                    
                    if (($currentStock + $originalQuantity) < $value) {
                        $fail('Stok tidak mencukupi di gudang yang dipilih.');
                    }
                },
            ],
        ]);

        DB::transaction(function () use ($request, $pengeluaranBarang) {
            // Revert old stock quantities
            foreach ($pengeluaranBarang->items as $oldItem) {
                Inventory::where('warehouse_id', $pengeluaranBarang->warehouse_id)
                         ->where('item_id', $oldItem->item_id)
                         ->increment('quantity', $oldItem->quantity);
            }

            $pengeluaranBarang->items()->delete();

            $pengeluaranBarang->update([
                'warehouse_id' => $request->warehouse_id,
                'stock_out_date' => $request->stock_out_date,
                'notes' => $request->notes,
            ]);

            foreach ($request->items as $itemData) {
                $pengeluaranBarang->items()->create([
                    'item_id' => $itemData['item_id'],
                    'quantity' => $itemData['quantity'],
                    'koli' => $itemData['koli'],
                ]);
                Inventory::where('warehouse_id', $request->warehouse_id)
                         ->where('item_id', $itemData['item_id'])
                         ->decrement('quantity', $itemData['quantity']);
            }
        });

        return redirect()->route('admin.stok-keluar.pengeluaran-barang.index')->with('success', 'Data pengeluaran barang berhasil diperbarui.');
    }

    public function destroy(StockOut $pengeluaranBarang)
    {
        DB::transaction(function () use ($pengeluaranBarang) {
            foreach ($pengeluaranBarang->items as $item) {
                Inventory::where('warehouse_id', $pengeluaranBarang->warehouse_id)
                         ->where('item_id', $item->item_id)
                         ->increment('quantity', $item->quantity);
            }
            $pengeluaranBarang->delete();
        });

        return redirect()->route('admin.stok-keluar.pengeluaran-barang.index')->with('success', 'Data pengeluaran barang berhasil dihapus.');
    }
}
