<?php

namespace App\Http\Controllers\Admin\Masterdata;

use App\Http\Controllers\Controller;
use App\Models\Jabatan;
use App\Models\User;
use App\Models\UserActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('jabatan')->get();
        return view('admin.masterdata.users.index', compact('users'));
    }

    public function create()
    {
        $jabatans = Jabatan::all();
        return view('admin.masterdata.users.create', compact('jabatans'));
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8',
                'jabatan_id' => 'nullable|exists:jabatans,id',
            ]);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'jabatan_id' => $request->jabatan_id,
            ]);

            UserActivity::create([
                'user_id' => Auth::id(),
                'activity' => 'created',
                'menu' => 'users',
                'description' => 'Menambahkan pengguna baru: ' . $user->name,
                'ip_address' => $request->ip(),
                'user_agent' => $request->header('User-Agent'),
            ]);

            return redirect()->route('users.index')->with('success', 'Pengguna berhasil ditambahkan.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->withErrors(['error' => 'Gagal menambahkan pengguna: ' . $e->getMessage()]);
        }
    }

    public function edit(User $user)
    {
        $jabatans = Jabatan::all();
        return view('admin.masterdata.users.edit', compact('user', 'jabatans'));
    }

    public function update(Request $request, User $user)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
                'password' => 'nullable|string|min:8',
                'jabatan_id' => 'nullable|exists:jabatans,id',
            ]);

            $data = $request->except('password');
            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            }

            $user->update($data);

            UserActivity::create([
                'user_id' => Auth::id(),
                'activity' => 'updated',
                'menu' => 'users',
                'description' => 'Memperbarui pengguna: ' . $user->name,
                'ip_address' => $request->ip(),
                'user_agent' => $request->header('User-Agent'),
            ]);

            return redirect()->route('users.index')->with('success', 'Pengguna berhasil diperbarui.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->withErrors(['error' => 'Gagal memperbarui pengguna: ' . $e->getMessage()]);
        }
    }

    public function destroy(Request $request, User $user)
    {
        try {
            $userName = $user->name;
            $user->delete();

            UserActivity::create([
                'user_id' => Auth::id(),
                'activity' => 'deleted',
                'menu' => 'users',
                'description' => 'Menghapus pengguna: ' . $userName,
                'ip_address' => $request->ip(),
                'user_agent' => $request->header('User-Agent'),
            ]);

            return redirect()->route('users.index')->with('success', 'Pengguna berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Gagal menghapus pengguna: ' . $e->getMessage()]);
        }
    }
}