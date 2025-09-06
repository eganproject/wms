<?php

namespace App\Http\Controllers\Admin\TransferGudang;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use Illuminate\Http\Request;
use App\Models\TransferRequest;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;

class PermintaanMasukController extends Controller
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

            if (auth()->user()->warehouse_id) {
                $query->where('tr.from_warehouse_id', auth()->user()->warehouse_id);
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

            foreach ($data as $row) {
                $row->action = '<a href="' . route('admin.transfergudang.permintaan-masuk.show', $row->id) . '" class="btn btn-info btn-sm">Detail</a>';
            }

            return response()->json([
                'draw' => intval($draw),
                'recordsTotal' => intval($totalRecords),
                'recordsFiltered' => intval($totalFiltered),
                'data' => $data,
            ]);
        }

        $warehouses = Warehouse::all();
        return view('admin.transfergudang.permintaan-masuk.index', compact('warehouses'));
    }

    public function show(TransferRequest $transferRequest)
    {
        $transferRequest->load(['fromWarehouse', 'toWarehouse', 'requester', 'items.item']);
        return view('admin.transfergudang.permintaan-masuk.show', compact('transferRequest'));
    }

    public function updateStatus(Request $request, TransferRequest $transferRequest)
    {
        $request->validate([
            'status' => 'required|in:approved',
        ]);

        $newStatus = $request->status;

        if ($newStatus === 'approved' && $transferRequest->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Hanya permintaan dengan status pending yang bisa disetujui.'], 422);
        }

        try {
            $transferRequest->status = $newStatus;
            $transferRequest->save();

            return response()->json(['success' => true, 'message' => 'Status berhasil diperbarui.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal memperbarui status: ' . $e->getMessage()], 500);
        }
    }

    public function createShipment(Request $request, TransferRequest $transferRequest)
    {
        $request->validate([
            'shipping_date' => 'required|date',
            'vehicle_type' => 'required|string|max:255',
            'license_plate' => 'required|string|max:255',
            'driver_name' => 'nullable|string|max:255',
            'driver_contact' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        if ($transferRequest->status !== 'approved') {
            return response()->json(['success' => false, 'message' => 'Hanya permintaan yang sudah disetujui yang bisa dikirim.'], 422);
        }

        try {
            DB::beginTransaction();

            Shipment::create([
                'transfer_request_id' => $transferRequest->id,
                'shipping_date' => $request->shipping_date,
                'vehicle_type' => $request->vehicle_type,
                'license_plate' => $request->license_plate,
                'driver_name' => $request->driver_name,
                'driver_contact' => $request->driver_contact,
                'description' => $request->description,
                'shipped_by' => auth()->id(),
            ]);

            $transferRequest->status = 'shipped';
            $transferRequest->shipped_at = now();
            $transferRequest->save();

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Data pengiriman berhasil disimpan dan status permintaan telah diubah menjadi dikirim.']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal membuat data pengiriman: ' . $e->getMessage()], 500);
        }
    }
}
