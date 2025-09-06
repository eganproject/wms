<?php

namespace App\Http\Controllers\Admin\StokMasuk;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TransferRequest;
use App\Models\Warehouse;
use App\Models\Inventory;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class PenerimaanTransferController extends Controller
{
    public function index(Request $request)
    {
        
        if ($request->ajax()) {
            $searchValue = $request->input('search.value', '');
            $start = $request->input('start', 0);
            $length = $request->input('length', 10);
            $draw = $request->input('draw', 0);
            $toWarehouseFilter = $request->input('to_warehouse_id');
            $fromWarehouseFilter = $request->input('from_warehouse_id');
            $statusFilter = $request->input('status'); // Get status filter from request
            $dateFilter = $request->input('date');

            $columns = [
                0 => 'tr.code',
                1 => 'tr.date',
                2 => 'from_warehouse_name',
                3 => 'to_warehouse_name',
                4 => 'tr.status',
                5 => 'requester_name',
                6 => 'tr.id',
            ];
            $orderByColumnIndex = $request->input('order.0.column', 0);
            $orderByColumnName = $columns[$orderByColumnIndex] ?? $columns[0];
            $orderDirection = $request->input('order.0.dir', 'asc');

            $query = TransferRequest::query()
                ->from('transfer_requests as tr')
                ->leftJoin('warehouses as fw', 'tr.from_warehouse_id', '=', 'fw.id')
                ->leftJoin('warehouses as tw', 'tr.to_warehouse_id', '=', 'tw.id')
                ->leftJoin('users as u', 'tr.requested_by', '=', 'u.id');
                // Removed hardcoded ->where('tr.status', 'shipped');

            if(auth()->user()->warehouse_id) {
                $query->where('tr.to_warehouse_id', auth()->user()->warehouse_id);
            }

            // Apply status filter
            if ($statusFilter && $statusFilter !== 'semua') {
                $query->where('tr.status', $statusFilter);
            } else { // Default to shipped and completed if 'semua' or no filter is provided
                $query->whereIn('tr.status', ['shipped', 'completed']);
            }

            $totalRecords = $query->count();

            if (!empty($searchValue)) {
                $query->where(function ($q) use ($searchValue) {
                    $q->where('tr.code', 'LIKE', "%{$searchValue}%")
                        ->orWhere('fw.name', 'LIKE', "%{$searchValue}%")
                        ->orWhere('tw.name', 'LIKE', "%{$searchValue}%")
                        ->orWhere('u.name', 'LIKE', "%{$searchValue}%");
                });
            }

            if ($fromWarehouseFilter && $fromWarehouseFilter !== 'semua') {
                $query->where('tr.from_warehouse_id', $fromWarehouseFilter);
            }

            if ($toWarehouseFilter && $toWarehouseFilter !== 'semua') {
                $query->where('tr.to_warehouse_id', $toWarehouseFilter);
            }

            if ($dateFilter && $dateFilter !== 'semua') {
                $query->whereDate('tr.date', $dateFilter);
            }

            $totalFiltered = $query->count();

            $data = $query->select(
                'tr.id',
                'tr.code',
                'tr.date',
                'tr.status',
                'fw.name as from_warehouse_name',
                'tw.name as to_warehouse_name',
                'u.name as requester_name'
            )
                ->orderBy($orderByColumnName, $orderDirection)
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
        
        return view('admin.stok_masuk.penerimaan_transfer.index', compact('warehouses'));
    }

    public function show(TransferRequest $transferRequest)
    {
        $transferRequest->load(['fromWarehouse', 'toWarehouse', 'requester', 'items.item']);
        return view('admin.stok_masuk.penerimaan_transfer.show', compact('transferRequest'));
    }

    public function updateStatus(Request $request, TransferRequest $transferRequest)
    {
        $request->validate([
            'status' => 'required|in:completed',
        ]);

        if ($transferRequest->status !== 'shipped') {
            return response()->json(['success' => false, 'message' => 'Hanya permintaan yang sudah dikirim yang bisa diselesaikan.'], 422);
        }

        DB::beginTransaction();
        try {
            $transferRequest->status = 'completed';
            $transferRequest->save();

            foreach ($transferRequest->items as $item) {
                // Decrease stock from source warehouse
                $fromInventory = Inventory::where('warehouse_id', $transferRequest->from_warehouse_id)
                    ->where('item_id', $item->item_id)
                    ->first();
                
                if ($fromInventory) {
                    $fromInventory->quantity -= $item->quantity;
                    $fromInventory->save();

                    StockMovement::create([
                        'item_id' => $item->item_id,
                        'warehouse_id' => $transferRequest->from_warehouse_id,
                        'quantity' => -$item->quantity,
                        'type' => 'transfer_out',
                        'reference_id' => $transferRequest->id,
                        'reference_type' => TransferRequest::class,
                    ]);
                }

                // Increase stock in destination warehouse
                $toInventory = Inventory::firstOrCreate(
                    ['warehouse_id' => $transferRequest->to_warehouse_id, 'item_id' => $item->item_id],
                    ['quantity' => 0]
                );
                $toInventory->quantity += $item->quantity;
                $toInventory->save();

                StockMovement::create([
                    'item_id' => $item->item_id,
                    'warehouse_id' => $transferRequest->to_warehouse_id,
                    'quantity' => $item->quantity,
                    'type' => 'transfer_in',
                    'reference_id' => $transferRequest->id,
                    'reference_type' => TransferRequest::class,
                ]);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Status berhasil diperbarui.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal memperbarui status: ' . $e->getMessage()], 500);
        }
    }
}