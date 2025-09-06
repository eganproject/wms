<?php

namespace App\Http\Controllers\Admin\ManajemenStok;

use App\Http\Controllers\Controller;
use App\Models\Adjustment;
use App\Models\AdjustmentItem;
use App\Models\Inventory;
use App\Models\Item;
use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Models\UserActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class AdjustmentController extends Controller
{
    private function generateNewCode()
    {
        $prefix = 'ADJ';
        $date = now()->format('Ymd');
        $latestAdjustment = Adjustment::where('code', 'LIKE', "$prefix-$date-%")->latest('id')->first();

        if ($latestAdjustment) {
            $sequence = (int) substr($latestAdjustment->code, -4) + 1;
        } else {
            $sequence = 1;
        }

        return sprintf('%s-%s-%04d', $prefix, $date, $sequence);
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $searchValue = $request->input('search.value', '');
            $start = $request->input('start', 0);
            $length = $request->input('length', 10);
            $draw = $request->input('draw', 0);
            $statusFilter = $request->input('status');
            $dateFilter = $request->input('date');

            // Define columns for sorting
            $columns = [
                0 => 'adj.code',
                1 => 'adj.adjustment_date',
                2 => 'warehouse_name',
                3 => 'items_name',
                4 => 'adj.status',
                5 => 'adj.id', // Actions column, not sortable
            ];
            $orderByColumnIndex = $request->input('order.0.column', 0);
            $orderByColumnName = $columns[$orderByColumnIndex] ?? $columns[0];
            $orderDirection = $request->input('order.0.dir', 'asc');

            // Base query
            $query = Adjustment::query()->from('adjustments as adj')
                ->leftJoin('warehouses as w', 'adj.warehouse_id', '=', 'w.id')
                ->leftJoin('adjustment_items as ai', 'adj.id', '=', 'ai.adjustment_id')
                ->leftJoin('items as i', 'ai.item_id', '=', 'i.id')
                ->groupBy('adj.id', 'adj.code', 'adj.adjustment_date', 'adj.status', 'w.name');

            // Total records
            $totalRecords = Adjustment::count();

            // Apply filters
            if (!empty($searchValue)) {
                $query->where(function ($q) use ($searchValue) {
                    $q->where('adj.code', 'LIKE', "%{$searchValue}%")
                        ->orWhere('adj.adjustment_date', 'LIKE', "%{$searchValue}%")
                        ->orWhere('w.name', 'LIKE', "%{$searchValue}%")
                        ->orWhere('adj.status', 'LIKE', "%{$searchValue}%");
                });
            }

            if ($statusFilter && $statusFilter !== 'semua') {
                $query->where('adj.status', $statusFilter);
            }

            if ($dateFilter && $dateFilter !== 'semua') {
                if (strpos($dateFilter, ' to ') !== false) {
                    [$startDate, $endDate] = explode(' to ', $dateFilter);
                    $query->whereBetween('adj.adjustment_date', [$startDate, $endDate]);
                } else {
                    $query->whereDate('adj.adjustment_date', $dateFilter);
                }
            }

            // Total filtered records
            $totalFiltered = (clone $query)->count();


            // Data query
            $data = $query->select(
                'adj.id',
                'adj.code',
                'adj.adjustment_date',
                'adj.status',
                'w.name as warehouse_name',
                DB::raw('GROUP_CONCAT(i.nama_barang, " (Qty:", FORMAT(ai.quantity,0), ")" SEPARATOR ", ") as items_name')
            )->orderBy($orderByColumnName, $orderDirection)
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

        return view('admin.manajemenstok.adjustment.index');
    }

    public function create()
    {
        $warehouses = Warehouse::all();
        $items = Item::with('uom')->get();
        $newCode = $this->generateNewCode();
        return view('admin.manajemenstok.adjustment.create', compact('warehouses', 'items', 'newCode'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|unique:adjustments,code',
            'adjustment_date' => 'required|date',
            'warehouse_id' => 'required|exists:warehouses,id',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|numeric',
            'items.*.koli' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $adjustment = Adjustment::create([
                'code' => $request->code,
                'adjustment_date' => $request->adjustment_date,
                'warehouse_id' => $request->warehouse_id,
                'user_id' => auth()->id(),
                'notes' => $request->notes,
                'status' => 'pending'
            ]);

            foreach ($request->items as $itemData) {
                AdjustmentItem::create([
                    'adjustment_id' => $adjustment->id,
                    'item_id' => $itemData['item_id'],
                    'quantity' => $itemData['quantity'],
                    'koli' => $itemData['koli'] ?? null,
                ]);
            }

            UserActivity::create([
                'user_id' => auth()->id(),
                'activity' => 'Membuat penyesuaian stok baru dengan kode ' . $adjustment->code,
                'menu' => 'Penyesuaian Stok'
            ]);

            DB::commit();
            return redirect()
                ->route('admin.manajemenstok.adjustment.index')
                ->with('success', 'Penyesuaian stok berhasil dibuat.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->with('error', 'Gagal membuat penyesuaian stok: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show(Adjustment $adjustment)
    {
        $adjustment->load('warehouse', 'adjustmentItems.item', 'user');
        return view('admin.manajemenstok.adjustment.show', compact('adjustment'));
    }

    public function edit(Adjustment $adjustment)
    {
        if ($adjustment->status !== 'pending') {
            return redirect()
                ->route('admin.manajemenstok.adjustment.index')
                ->with('error', 'Hanya penyesuaian dengan status pending yang dapat diubah.');
        }

        $adjustment->load('adjustmentItems.item');
        $warehouses = Warehouse::all();
        $items = Item::with('uom')->get();
        return view('admin.manajemenstok.adjustment.edit', compact('adjustment', 'warehouses', 'items'));
    }

    public function update(Request $request, Adjustment $adjustment)
    {
        if ($adjustment->status !== 'pending') {
            return redirect()
                ->route('admin.manajemenstok.adjustment.index')
                ->with('error', 'Hanya penyesuaian dengan status pending yang dapat diubah.');
        }

        $request->validate([
            'code' => 'required|unique:adjustments,code,' . $adjustment->id,
            'adjustment_date' => 'required|date',
            'warehouse_id' => 'required|exists:warehouses,id',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|numeric',
            'items.*.koli' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $adjustment->update([
                'code' => $request->code,
                'adjustment_date' => $request->adjustment_date,
                'warehouse_id' => $request->warehouse_id,
                'notes' => $request->notes
            ]);

            // Delete existing items
            $adjustment->adjustmentItems()->delete();

            // Create new items
            foreach ($request->items as $itemData) {
                AdjustmentItem::create([
                    'adjustment_id' => $adjustment->id,
                    'item_id' => $itemData['item_id'],
                    'quantity' => $itemData['quantity'],
                    'koli' => $itemData['koli'] ?? null,
                ]);
            }

            UserActivity::create([
                'user_id' => auth()->id(),
                'activity' => 'Mengubah penyesuaian stok dengan kode ' . $adjustment->code,
                'menu' => 'Penyesuaian Stok'
            ]);

            DB::commit();
            return redirect()
                ->route('admin.manajemenstok.adjustment.index')
                ->with('success', 'Penyesuaian stok berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->with('error', 'Gagal memperbarui penyesuaian stok: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy(Adjustment $adjustment)
    {
        if ($adjustment->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya penyesuaian dengan status pending yang dapat dihapus.'
            ], 422);
        }

        DB::beginTransaction();
        try {
            UserActivity::create([
                'user_id' => auth()->id(),
                'activity' => 'Menghapus penyesuaian stok dengan kode ' . $adjustment->code,
                'menu' => 'Penyesuaian Stok'
            ]);

            $adjustment->adjustmentItems()->delete();
            $adjustment->delete();

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Penyesuaian stok berhasil dihapus.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus penyesuaian stok: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateStatus(Request $request, Adjustment $adjustment)
    {
        if ($adjustment->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Status penyesuaian sudah diubah sebelumnya.'
            ], 422);
        }

        $request->validate([
            'status' => 'required|in:completed',
        ]);

        DB::beginTransaction();
        try {
            $adjustment->load('adjustmentItems.item');

            foreach ($adjustment->adjustmentItems as $item) {
                $inventory = Inventory::firstOrNew([
                    'warehouse_id' => $adjustment->warehouse_id,
                    'item_id' => $item->item_id
                ]);

                if (!$inventory->exists && $item->quantity < 0) {
                    throw new \Exception("Tidak dapat mengurangi stok. Stok tidak ditemukan untuk item: " . $item->item->name);
                }

                StockMovement::create([
                    'warehouse_id' => $adjustment->warehouse_id,
                    'item_id' => $item->item_id,
                    'date' => now(),
                    'quantity' => abs($item->quantity),
                    'koli' => $item->koli ?? 0,
                    'type' => $item->quantity >= 0 ? 'stock_in' : 'stock_out',
                    'description' => 'Penyesuaian stok: ' . $adjustment->code,
                    'user_id' => auth()->id(),
                    'reference_id' => $item->id,
                    'reference_type' => 'adjustment_items'
                ]);

                $inventory->quantity = ($inventory->quantity ?? 0) + $item->quantity;
                $inventory->koli = ($inventory->koli ?? 0) + ($item->koli ?? 0);
                $inventory->save();
            }

            $adjustment->status = 'completed';
            $adjustment->completed_at = now();
            $adjustment->save();

            UserActivity::create([
                'user_id' => auth()->id(),
                'activity' => 'Menyelesaikan penyesuaian stok dengan kode ' . $adjustment->code,
                'menu' => 'Penyesuaian Stok'
            ]);

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Status penyesuaian berhasil diperbarui.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui status: ' . $e->getMessage()
            ], 500);
        }
    }
}