<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User; // <-- Import model User

class UserController extends Controller
{
    /**
     * Menampilkan halaman daftar user beserta statistik.
     */
    public function index()
    {
        $users = User::all();
        $adminCount = $users->where('is_admin', true)->count();

        // User dianggap online jika aktivitas terakhirnya dalam 5 menit terakhir
        $onlineUsers = User::where('is_admin', false)
                            ->where('last_seen', '>=', now()->subMinutes(5))
                            ->count();

        $offlineUsers = User::where('is_admin', false)
                             ->where(function ($query) {
                                 $query->where('last_seen', '<', now()->subMinutes(5))
                                       ->orWhereNull('last_seen');
                             })
                             ->count();

        return view('admin.users.index', compact('users', 'adminCount', 'onlineUsers', 'offlineUsers'));
    }
    // ... di dalam class UserController ...

    // Method index() yang sudah kita perbarui

    /**
     * Menampilkan form untuk mengedit data user.
     */
    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Mengupdate data user di database.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'is_admin' => 'required|boolean',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->is_admin = $request->is_admin;

        // Hanya update password jika diisi
        if ($request->filled('password')) {
            $user->password = bcrypt($request->password);
        }

        $user->save();

        return redirect()->route('admin.users.index')
                         ->with('success', 'Data user berhasil diupdate.');
    }
}
