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

        // Calculate statistics - PERBAIKAN: Gunakan last_login_at instead of last_seen
        $adminCount = User::where('is_admin', true)->count();

        // Count online users (logged in within last 5 minutes)
        $onlineUsers = User::where('last_login_at', '>=', now()->subMinutes(5))->count();

        // Count offline users
        $offlineUsers = User::where(function ($query) {
            $query->where('last_login_at', '<', now()->subMinutes(5))
                  ->orWhereNull('last_login_at');
        })->count();

        return view('admin.users.index', compact('users', 'adminCount', 'onlineUsers', 'offlineUsers'));
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'is_admin' => ['required', 'boolean'],
        ];

        // Only validate password if it's provided
        if ($request->filled('password')) {
            $rules['password'] = ['required', 'confirmed', Rules\Password::defaults()];
        }

        $request->validate($rules);

        try {
            $updateData = [
                'name' => $request->name,
                'email' => $request->email,
                'is_admin' => $request->boolean('is_admin'),
            ];

            // Only update password if provided
            if ($request->filled('password')) {
                $updateData['password'] = Hash::make($request->password);
            }

            $user->update($updateData);

            return redirect()->route('admin.users.index')
                           ->with('success', 'Data user berhasil diperbarui!');

        } catch (\Exception $e) {
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Gagal memperbarui user: ' . $e->getMessage());
        }
    }

    public function destroy(User $user)
    {
        // Prevent admin from deleting themselves
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                           ->with('error', 'Anda tidak dapat menghapus akun Anda sendiri!');
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
