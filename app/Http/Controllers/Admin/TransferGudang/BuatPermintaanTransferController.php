<?php

namespace App\Http\Controllers\Admin\TransferGudang;

use App\Http\Controllers\Controller;
use App\Models\TransferRequest;
use App\Models\TransferRequestItem;
use App\Models\Warehouse;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BuatPermintaanTransferController extends Controller
{
    public function index()
    {
        $transferRequests = TransferRequest::with(['fromWarehouse', 'toWarehouse', 'requester'])->latest()->paginate(10);
        return view('admin.transfergudang.buat-permintaan.index', compact('transferRequests'));
    }

    public function create()
    {
        $warehouses = Warehouse::all();
        $items = Item::all();
        $code = 'TR-' . date('Ymd') . '-' . str_pad(TransferRequest::count() + 1, 4, '0', STR_PAD_LEFT);
        return view('admin.transfergudang.buat-permintaan.create', compact('warehouses', 'items', 'code'));
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

            return redirect()->route('admin.transfergudang.buat-permintaan-transfer.index')->with('success', 'Permintaan transfer berhasil dibuat.');

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
        $transferRequest->load(['fromWarehouse', 'toWarehouse', 'requester', 'items.item']);
        return view('admin.transfergudang.buat-permintaan.show', compact('transferRequest'));
    }

    public function edit(TransferRequest $transferRequest)
    {
        $warehouses = Warehouse::all();
        $items = Item::all();
        $transferRequest->load('items');
        return view('admin.transfergudang.buat-permintaan.edit', compact('transferRequest', 'warehouses', 'items'));
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

            return redirect()->route('admin.transfergudang.buat-permintaan-transfer.index')->with('success', 'Permintaan transfer berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal memperbarui permintaan transfer: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(TransferRequest $transferRequest)
    {
        if ($transferRequest->status != 'pending') {
            return redirect()->route('admin.transfergudang.buat-permintaan-transfer.index')->with('error', 'Hanya permintaan dengan status pending yang dapat dihapus.');
        }

        try {
            DB::beginTransaction();
            $transferRequest->items()->delete();
            $transferRequest->delete();
            DB::commit();
            return redirect()->route('admin.transfergudang.buat-permintaan-transfer.index')->with('success', 'Permintaan transfer berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('admin.transfergudang.buat-permintaan-transfer.index')->with('error', 'Gagal menghapus permintaan transfer.');
        }
    }
}