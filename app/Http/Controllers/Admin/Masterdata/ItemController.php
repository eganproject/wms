<?php

namespace App\Http\Controllers\Admin\Masterdata;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Uom;
use Illuminate\Http\Request;

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
        return view('admin.masterdata.items.create', compact('uoms'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'koli' => 'required|integer',
            'sku' => 'required|string|unique:items,sku',
            'uom_id' => 'required|exists:uoms,id',
            'nama_barang' => 'required|string',
            'deskripsi' => 'nullable|string',
            'product_code' => 'required|string|unique:items,product_code',
        ]);

        Item::create($request->all());

        return redirect()->route('admin.masterdata.items.index')
            ->with('success', 'Item created successfully.');
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
        $item = Item::findOrFail($id);

        $request->validate([
            'koli' => 'required|integer',
            'sku' => 'required|string|unique:items,sku,' . $item->id,
            'uom_id' => 'required|exists:uoms,id',
            'nama_barang' => 'required|string',
            'deskripsi' => 'nullable|string',
            'product_code' => 'required|string|unique:items,product_code,' . $item->id,
        ]);

        $item->update($request->all());

        return redirect()->route('admin.masterdata.items.index')
            ->with('success', 'Item updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $item = Item::findOrFail($id);
        $item->delete();

        return redirect()->route('admin.masterdata.items.index')
            ->with('success', 'Item deleted successfully.');
    }
}
