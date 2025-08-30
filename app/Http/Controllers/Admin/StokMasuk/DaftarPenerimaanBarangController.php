<?php


namespace App\Http\Controllers\Admin\StokMasuk;

use App\Http\Controllers\Controller;

use App\Models\StockInOrder;
use App\Models\Warehouse;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DaftarPenerimaanBarangController extends Controller
{
    private function generateNewCode()
    {
        $prefix = 'IN';
        $date = now()->format('Ymd');
        $latestOrder = StockInOrder::where('code', 'LIKE', "$prefix-$date-%")->latest('id')->first();

        if ($latestOrder) {
            $sequence = (int) substr($latestOrder->code, -4) + 1;
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
                0 => 'sio.code',
                1 => 'sio.date',
                2 => 'warehouse_name',
                3 => 'sio.status',
                4 => 'sio.id', // Actions column, not sortable
            ];
            $orderByColumnIndex = $request->input('order.0.column', 0);
            $orderByColumnName = $columns[$orderByColumnIndex] ?? $columns[0];
            $orderDirection = $request->input('order.0.dir', 'asc');

            // Base query
            $query = StockInOrder::query()->from('stock_in_orders as sio')->leftJoin('warehouses as w', 'sio.warehouse_id', '=', 'w.id');

            // Total records
            $totalRecords = $query->count();

            // Apply filters
            if (!empty($searchValue)) {
                $query->where(function($q) use ($searchValue) {
                    $q->where('sio.code', 'LIKE', "%{$searchValue}%")
                      ->orWhere('sio.date', 'LIKE', "%{$searchValue}%")
                      ->orWhere('w.name', 'LIKE', "%{$searchValue}%")
                      ->orWhere('sio.status', 'LIKE', "%{$searchValue}%");
                });
            }

            if ($statusFilter && $statusFilter !== 'semua') {
                $query->where('sio.status', $statusFilter);
            }

            if ($dateFilter) {
                $query->whereDate('sio.date', $dateFilter);
            }

            // Total filtered records
            $totalFiltered = $query->count();

            // Data query
            $data = $query->select('sio.id', 'sio.code', 'sio.date', 'sio.status', 'w.name as warehouse_name')
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

        // If not an AJAX request, return the view as before
        return view('admin.stok_masuk.daftar_penerimaan_barang.index');
    }

    public function show(StockInOrder $stockInOrder)
    {
        $stockInOrder->load('warehouse', 'items.item', 'requestedBy.jabatan');
        return view('admin.stok_masuk.daftar_penerimaan_barang.show', compact('stockInOrder'));
    }

    public function create()
    {
        $warehouses = Warehouse::all();
        $items = Item::all();
        $newCode = $this->generateNewCode();
        return view('admin.stok_masuk.daftar_penerimaan_barang.create', compact('warehouses', 'items', 'newCode'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'warehouse_id' => 'required|exists:warehouses,id',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.koli' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $stockInOrder = StockInOrder::create([
                'code' => $this->generateNewCode(),
                'date' => $request->date,
                'warehouse_id' => $request->warehouse_id,
                'description' => $request->description,
                'status' => 'requested', // Default status
                'requested_by' => auth()->id(),
            ]);

            foreach ($request->items as $itemData) {
                $stockInOrder->items()->create($itemData);
            }

            DB::commit();

            return redirect()->route('admin.stok-masuk.daftar-penerimaan-barang.index')->with('success', 'Penerimaan barang berhasil dibuat.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal membuat penerimaan barang: ' . $e->getMessage())->withInput();
        }
    }

    public function edit(StockInOrder $stockInOrder)
    {
        $stockInOrder->load('items.item');
        $warehouses = Warehouse::all();
        $items = Item::all();
        return view('admin.stok_masuk.daftar_penerimaan_barang.edit', compact('stockInOrder', 'warehouses', 'items'));
    }

    public function update(Request $request, StockInOrder $stockInOrder)
    {
        $request->validate([
            'code' => 'required|unique:stock_in_orders,code,' . $stockInOrder->id,
            'date' => 'required|date',
            'warehouse_id' => 'required|exists:warehouses,id',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.koli' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $stockInOrder->update([
                'code' => $request->code,
                'date' => $request->date,
                'warehouse_id' => $request->warehouse_id,
                'description' => $request->description,
            ]);

            // Hapus item lama dan buat yang baru
            $stockInOrder->items()->delete();
            foreach ($request->items as $itemData) {
                $stockInOrder->items()->create($itemData);
            }

            DB::commit();

            return redirect()->route('admin.stok-masuk.daftar-penerimaan-barang.index')->with('success', 'Penerimaan barang berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal memperbarui penerimaan barang: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(StockInOrder $stockInOrder)
    {
        DB::beginTransaction();
        try {
            $stockInOrder->items()->delete();
            $stockInOrder->delete();
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Dokumen berhasil dihapus.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal menghapus dokumen.'], 500);
        }
    }

    public function updateStatus(Request $request, StockInOrder $stockInOrder)
    {
        $request->validate([
            'status' => 'required|in:shipped,completed',
        ]);

        try {
            $stockInOrder->status = $request->status;
            $stockInOrder->save();

            return response()->json(['success' => true, 'message' => 'Status berhasil diperbarui.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal memperbarui status: ' . $e->getMessage()], 500);
        }
    }
}
