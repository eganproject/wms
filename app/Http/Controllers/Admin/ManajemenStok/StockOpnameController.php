<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StockOpnameController extends Controller
{
    public function index()
    {
        return view('admin.manajemenstok.stok-opname.index');
    }
}
