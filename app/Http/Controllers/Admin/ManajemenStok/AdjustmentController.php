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

            $query = DB::table('adjustments as adj')
                ->leftJoin('warehouses as w', 'adj.warehouse_id', '=', 'w.id')
                ->leftJoin('users as u', 'adj.user_id', '=', 'u.id')
                ->leftJoin('adjustment_items as ai', 'adj.id', '=', 'ai.adjustment_id')
                ->leftJoin('items as i', 'ai.item_id', '=', 'i.id');

            if (!empty($searchValue)) {
                $query->where(function ($q) use ($searchValue) {
                    $q->where('adj.code', 'LIKE', "%{$searchValue}%")
                        ->orWhere('adj.adjustment_date', 'LIKE', "%{$searchValue}%")
                        ->orWhere('w.name', 'LIKE', "%{$searchValue}%")
                        ->orWhere('u.name', 'LIKE', "%{$searchValue}%")
                        ->orWhere('adj.status', 'LIKE', "%{$searchValue}%");
                });
            }

            if ($statusFilter && $statusFilter !== 'semua') {
                $query->where('adj.status', $statusFilter);
            }

            if ($dateFilter && $dateFilter !== 'semua') {
                if (strpos($dateFilter, ' to ') !== false) {
                    [$startDate, $endDate] = explode(' to ', $dateFilter);
                    $query->whereBetween('adj.adjustment_date', [
                        Carbon::parse($startDate)->startOfDay(),
                        Carbon::parse($endDate)->endOfDay()
                    ]);
                } else {
                    $query->whereDate('adj.adjustment_date', $dateFilter);
                }
            }

            $totalRecords = Adjustment::count();
            $totalFilteredQuery = clone $query;
            $totalFiltered = $totalFilteredQuery->count(DB::raw('DISTINCT adj.id'));

            $data = $query->select([
                'adj.id',
                'adj.code',
                'adj.adjustment_date',
                'adj.status',
                'w.name as warehouse_name',
                'u.name as user_name',
                DB::raw('GROUP_CONCAT(CONCAT(i.name, " (", ai.quantity, ")") SEPARATOR ", ") as items_name')
            ])
                ->groupBy('adj.id', 'adj.code', 'adj.adjustment_date', 'adj.status', 'w.name', 'u.name')
                ->orderBy('adj.created_at', 'desc')
                ->offset($start)
                ->limit($length)
                ->get();

            $formattedData = $data->map(function ($item) {
                $statusBadge = match($item->status) {
                    'pending' => '<span class="badge badge-warning">Pending</span>',
                    'completed' => '<span class="badge badge-success">Completed</span>',
                    default => '<span class="badge badge-secondary">' . ucfirst($item->status) . '</span>'
                };

                $actions = '';
                if ($item->status === 'pending') {
                    $actions .= '<a href="' . route('admin.manajemenstok.adjustment.edit', $item->id) . '" class="btn btn-sm btn-primary me-2">Edit</a>';
                }
                $actions .= '<a href="' . route('admin.manajemenstok.adjustment.show', $item->id) . '" class="btn btn-sm btn-info">Detail</a>';

                return [
                    'id' => $item->id,
                    'code' => $item->code,
                    'adjustment_date' => Carbon::parse($item->adjustment_date)->format('d/m/Y'),
                    'warehouse_name' => $item->warehouse_name,
                    'user_name' => $item->user_name,
                    'items_name' => $item->items_name,
                    'status' => $statusBadge,
                    'actions' => $actions
                ];
            });

            return response()->json([
                'draw' => intval($draw),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $totalFiltered,
                'data' => $formattedData
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
            'items.*.uom_id' => 'required|exists:uoms,id',
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
                    'uom_id' => $itemData['uom_id']
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
            'items.*.uom_id' => 'required|exists:uoms,id',
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
                    'uom_id' => $itemData['uom_id']
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