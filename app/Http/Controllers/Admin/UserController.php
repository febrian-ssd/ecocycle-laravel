<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('created_at', 'desc')->get();

        // Calculate statistics
        $adminCount = User::where('role', 'admin')->count();

        // Count online users - gunakan updated_at sebagai indikator aktivitas terakhir
        $onlineUsers = User::where('updated_at', '>=', now()->subMinutes(5))->count();

        // Count offline users
        $offlineUsers = User::where(function ($query) {
            $query->where('updated_at', '<', now()->subMinutes(5))
                  ->orWhereNull('updated_at');
        })->count();

        return view('admin.users.index', compact('users', 'adminCount', 'onlineUsers', 'offlineUsers'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'phone_number' => ['nullable', 'string'],
            'address' => ['nullable', 'string'],
            'role' => ['required', 'string', 'in:admin,user'],
        ]);

        // Mencegah semua admin dihapus
        if ($request->role !== 'admin') {
            $adminCount = User::where('role', 'admin')->count();
            if ($adminCount <= 1 && User::where('id', $request->id)->where('role', 'admin')->exists()) {
                return back()->withErrors(['role' => 'Tidak dapat mengubah peran admin terakhir.']);
            }
        }

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone_number' => $request->phone_number,
            'address' => $request->address,
            'role' => $request->role,
        ]);

        return redirect()->route('admin.users.index')->with('success', 'User berhasil ditambahkan.');
    }

    public function show(User $user)
    {
        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'phone_number' => ['nullable', 'string'],
            'address' => ['nullable', 'string'],
            'role' => ['required', 'string', 'in:admin,user'],
        ]);

        // Mencegah semua admin dihapus
        if ($request->role !== 'admin') {
            $adminCount = User::where('role', 'admin')->count();
            $currentUserIsAdmin = $user->isAdmin();
            if ($adminCount <= 1 && $currentUserIsAdmin) {
                return back()->withErrors(['role' => 'Tidak dapat mengubah peran admin terakhir.']);
            }
        }

        $data = $request->only('name', 'email', 'phone_number', 'address', 'role');
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        // ✅ PERBAIKAN: Gunakan route name yang konsisten
        return redirect()->route('admin.users.index')->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        // Prevent admin from deleting themselves
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                           ->with('error', 'Anda tidak dapat menghapus akun Anda sendiri!');
        }

        // Prevent deleting the last admin
        if ($user->isAdmin()) {
            $adminCount = User::where('role', 'admin')->count();
            if ($adminCount <= 1) {
                return redirect()->route('admin.users.index')
                               ->with('error', 'Tidak dapat menghapus admin terakhir!');
            }
        }

        try {
            $userName = $user->name;
            $user->delete();

            return redirect()->route('admin.users.index')
                           ->with('success', "User {$userName} berhasil dihapus!");

        } catch (\Exception $e) {
            return redirect()->route('admin.users.index')
                           ->with('error', 'Gagal menghapus user: ' . $e->getMessage());
        }
    }
}
