<?php


namespace App\Http\Controllers\Admin\StokMasuk;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

class DaftarPenerimaanBarangController extends Controller
{
    public function index()
    {
        return view('admin.stok_masuk.daftar_penerimaan_barang.index');
    }
}
