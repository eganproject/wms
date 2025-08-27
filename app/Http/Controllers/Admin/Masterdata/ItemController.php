<?php

namespace App\Http\Controllers\Admin\Masterdata;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Uom;
use App\Models\UserActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class ItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $items = Item::with('uom')->latest()->paginate(10);
        return view('admin.masterdata.items.index', compact('items'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $uoms = Uom::all();
        $generatedProductCode = $this->generateProductCode();
        return view('admin.masterdata.items.create', compact('uoms', 'generatedProductCode'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'koli' => 'required|integer',
                'sku' => 'required|string|unique:items,sku',
                'uom_id' => 'required|exists:uoms,id',
                'nama_barang' => 'required|string',
                'deskripsi' => 'nullable|string',
                'product_code' => 'nullable|string|unique:items,product_code',
            ]);

            $data = $request->all();
            // If product_code is not provided (e.g., from a hidden field or JS generation), generate it
            if (empty($data['product_code'])) {
                $data['product_code'] = $this->generateProductCode();
            }

            $item = Item::create($data);

            UserActivity::create([
                'user_id' => Auth::id(),
                'activity' => 'created',
                'menu' => 'items',
                'description' => 'Menambahkan item baru: ' . $item->nama_barang . ' (SKU: ' . $item->sku . ')',
                'ip_address' => $request->ip(),
                'user_agent' => $request->header('User-Agent'),
            ]);

            return redirect()->route('admin.masterdata.items.index')
                ->with('success', 'Item created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->withErrors(['error' => 'Gagal menambahkan item: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $item = Item::with('uom')->findOrFail($id);
        return view('admin.masterdata.items.show', compact('item'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $item = Item::findOrFail($id);
        $uoms = Uom::all();
        return view('admin.masterdata.items.edit', compact('item', 'uoms'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $item = Item::findOrFail($id);

            $request->validate([
                'koli' => 'required|integer',
                'sku' => 'required|string|unique:items,sku,' . $item->id,
                'uom_id' => 'required|exists:uoms,id',
                'nama_barang' => 'required|string',
                'deskripsi' => 'nullable|string',
                'product_code' => 'nullable|string|unique:items,product_code,' . $item->id,
            ]);

            $item->update($request->except(['product_code']));

            UserActivity::create([
                'user_id' => Auth::id(),
                'activity' => 'updated',
                'menu' => 'items',
                'description' => 'Memperbarui item: ' . $item->nama_barang . ' (SKU: ' . $item->sku . ')',
                'ip_address' => $request->ip(),
                'user_agent' => $request->header('User-Agent'),
            ]);

            return redirect()->route('admin.masterdata.items.index')
                ->with('success', 'Item updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->withErrors(['error' => 'Gagal memperbarui item: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request,$id)
    {
        try {
            $item = Item::findOrFail($id);
            $itemName = $item->nama_barang;
            $itemSku = $item->sku;
            $item->delete();

            UserActivity::create([
                'user_id' => Auth::id(),
                'activity' => 'deleted',
                'menu' => 'items',
                'description' => 'Menghapus item: ' . $itemName . ' (SKU: ' . $itemSku . ')',
                'ip_address' => $request->ip(),
                'user_agent' => $request->header('User-Agent'),
            ]);

            return redirect()->route('admin.masterdata.items.index')
                ->with('success', 'Item deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus item: ' . $e->getMessage());
        }
    }

    /**
     * Generate a unique product code.
     *
     * @return string
     */
    private function generateProductCode()
    {
        $prefix = 'PROD';
        $date = date('Ymd');

        $lastItem = Item::where('product_code', 'like', $prefix . $date . '%')
                        ->orderBy('product_code', 'desc')
                        ->first();

        if ($lastItem) {
            $lastCode = $lastItem->product_code;
            // Extract the numeric part from the end of the product code
            // Assuming the format is PRODYYYYMMDDXXXX where XXXX is the number
            $numericPart = substr($lastCode, -4); // Get the last 4 characters
            if (is_numeric($numericPart)) {
                $lastNumber = (int) $numericPart;
                $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
                return $prefix . $date . $newNumber;
            } else {
                // If the last 4 characters are not numeric, generate a new random 4-digit number
                return $prefix . $date . str_pad(mt_rand(0, 99), 2, '0', STR_PAD_LEFT);
            }
        } else {
            return $prefix . $date . str_pad(mt_rand(0, 99), 2, '0', STR_PAD_LEFT);
        }
    }
}