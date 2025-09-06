<?php

namespace App\Http\Controllers\Admin\TransferGudang;

use App\Http\Controllers\Controller;
use App\Models\TransferRequest;
use App\Models\TransferRequestItem;
use App\Models\Warehouse;
use App\Models\Item;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BuatPermintaanTransferController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $searchValue = $request->input('search.value', '');
            $start = $request->input('start', 0);
            $length = $request->input('length', 10);
            $draw = $request->input('draw', 0);
            $fromWarehouseFilter = $request->input('from_warehouse_id');
            $toWarehouseFilter = $request->input('to_warehouse_id');
            $statusFilter = $request->input('status');
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

            // Apply user's warehouse filter if not null
            if (auth()->user()->warehouse_id !== null) {
                $query->where(function ($q) {
                    $q->where('tr.from_warehouse_id', auth()->user()->warehouse_id)
                      ->orWhere('tr.to_warehouse_id', auth()->user()->warehouse_id);
                });
            }

            $totalRecords = $query->count();

            if (!empty($searchValue)) {
                $query->where(function ($q) use ($searchValue) {
                    $q->where('tr.code', 'LIKE', "%{$searchValue}%")
                        ->orWhere('fw.name', 'LIKE', "%{$searchValue}%")
                        ->orWhere('tw.name', 'LIKE', "%{$searchValue}%")
                        ->orWhere('u.name', 'LIKE', "%{$searchValue}%")
                        ->orWhere('tr.status', 'LIKE', "%{$searchValue}%");
                });
            }

            if ($fromWarehouseFilter && $fromWarehouseFilter !== 'semua') {
                $query->where('tr.from_warehouse_id', $fromWarehouseFilter);
            }

            if ($toWarehouseFilter && $toWarehouseFilter !== 'semua') {
                $query->where('tr.to_warehouse_id', $toWarehouseFilter);
            }

            if ($statusFilter && $statusFilter !== 'semua') {
                $query->where('tr.status', $statusFilter);
            }

            if ($dateFilter) {
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
        return view('admin.transfergudang.permintaan-terkirim.index', compact('warehouses'));
    }

    public function create()
    {
        $warehouses = Warehouse::all();
        $items = Item::all();
        $code = 'TR-' . date('Ymd') . '-' . str_pad(TransferRequest::count() + 1, 4, '0', STR_PAD_LEFT);
        return view('admin.transfergudang.permintaan-terkirim.create', compact('warehouses', 'items', 'code'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|unique:transfer_requests,code',
            'from_warehouse_id' => 'required|exists:warehouses,id',
            'to_warehouse_id' => 'required|exists:warehouses,id|different:from_warehouse_id',
            'date' => 'required|date',
            'description' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|numeric|gt:0',
            'items.*.koli' => 'nullable|numeric|min:0',
            'items.*.description' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $transferRequest = TransferRequest::create([
                'code' => $request->code,
                'from_warehouse_id' => $request->from_warehouse_id,
                'to_warehouse_id' => $request->to_warehouse_id,
                'date' => $request->date,
                'description' => $request->description,
                'status' => 'pending',
                'requested_by' => auth()->id(),
            ]);

            foreach ($request->items as $item) {
                TransferRequestItem::create([
                    'transfer_request_id' => $transferRequest->id,
                    'item_id' => $item['item_id'],
                    'quantity' => $item['quantity'],
                    'koli' => $item['koli'] ?? 0,
                    'description' => $item['description'],
                ]);
            }

            DB::commit();

            return redirect()->route('admin.transfergudang.permintaan-terkirim.index')->with('success', 'Permintaan transfer berhasil dibuat.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal membuat permintaan transfer: ' . $e->getMessage())->withInput();
        }
    }

    public function calculateItemValues(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:items,id',
            'quantity' => 'nullable|numeric|min:0',
            'koli' => 'nullable|numeric|min:0',
        ]);

        $item = Item::find($request->item_id);

        if (!$item) {
            return response()->json(['error' => 'Item not found'], 404);
        }

        $koliPerUnit = $item->koli ?? 1; // Use 'koli' column, default to 1 if null

        $quantity = $request->input('quantity');
        $koli = $request->input('koli');

        if (isset($quantity)) {
            // Calculate koli from quantity
            $calculatedKoli = $koliPerUnit > 0 ? $quantity / $koliPerUnit : 0;
            return response()->json([
                'quantity' => (float) $quantity,
                'koli' => (float) $calculatedKoli,
            ]);
        } elseif (isset($koli)) {
            // Calculate quantity from koli
            $calculatedQuantity = $koli * $koliPerUnit;
            return response()->json([
                'quantity' => (float) $calculatedQuantity,
                'koli' => (float) $koli,
            ]);
        }

        return response()->json(['error' => 'Invalid input. Either quantity or koli must be provided.'], 400);
    }

    public function show(TransferRequest $transferRequest)
    {
        $transferRequest->load(['fromWarehouse', 'toWarehouse', 'requester', 'items']);
        return view('admin.transfergudang.permintaan-terkirim.show', compact('transferRequest'));
    }

    public function edit(TransferRequest $transferRequest)
    {
        $warehouses = Warehouse::all();
        $items = Item::all();
        $transferRequest->load('items');
        return view('admin.transfergudang.permintaan-terkirim.edit', compact('transferRequest', 'warehouses', 'items'));
    }

    public function update(Request $request, TransferRequest $transferRequest)
    {
        $request->validate([
            'from_warehouse_id' => 'required|exists:warehouses,id',
            'to_warehouse_id' => 'required|exists:warehouses,id|different:from_warehouse_id',
            'date' => 'required|date',
            'description' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|numeric|gt:0',
            'items.*.koli' => 'nullable|numeric|min:0',
            'items.*.description' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $transferRequest->update([
                'from_warehouse_id' => $request->from_warehouse_id,
                'to_warehouse_id' => $request->to_warehouse_id,
                'date' => $request->date,
                'description' => $request->description,
                'status' => $request->status ?? $transferRequest->status,
            ]);

            $transferRequest->items()->delete();

            foreach ($request->items as $item) {
                TransferRequestItem::create([
                    'transfer_request_id' => $transferRequest->id,
                    'item_id' => $item['item_id'],
                    'quantity' => $item['quantity'],
                    'koli' => $item['koli'] ?? 0,
                    'description' => $item['description'],
                ]);
            }

            DB::commit();

            return redirect()->route('admin.transfergudang.permintaan-terkirim.index')->with('success', 'Permintaan transfer berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal memperbarui permintaan transfer: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(TransferRequest $transferRequest)
    {
        if ($transferRequest->status != 'pending') {
            return redirect()->route('admin.transfergudang.permintaan-terkirim.index')->with('error', 'Hanya permintaan dengan status pending yang dapat dihapus.');
        }

        try {
            DB::beginTransaction();
            $transferRequest->items()->delete();
            $transferRequest->delete();
            DB::commit();
            return redirect()->route('admin.transfergudang.permintaan-terkirim.index')->with('success', 'Permintaan transfer berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('admin.transfergudang.permintaan-terkirim.index')->with('error', 'Gagal menghapus permintaan transfer.');
        }
    }

    public function getItemsByWarehouse($warehouse_id)
    {
        $items = Inventory::with('item')
            ->where('warehouse_id', $warehouse_id)
            ->where('quantity', '>', 0)
            ->get();

        return response()->json($items);
    }
}