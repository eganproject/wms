<?php


namespace App\Http\Controllers\Admin\TransferGudang;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

class BuatPermintaanTransferController extends Controller
{
    public function index(){
        return view('admin.transfergudang.buatpermintaan.index');
    }
}
