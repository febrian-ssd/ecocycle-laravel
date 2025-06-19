<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all();
        $adminCount = $users->where('is_admin', true)->count();
        $onlineUsers = User::where('is_admin', false)->where('last_seen', '>=', now()->subMinutes(5))->count();
        $offlineUsers = User::where('is_admin', false)->where(function ($query) {
                                 $query->where('last_seen', '<', now()->subMinutes(5))->orWhereNull('last_seen');
                             })->count();
        return view('admin.users.index', compact('users', 'adminCount', 'onlineUsers', 'offlineUsers'));
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

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
        if ($request->filled('password')) {
            $user->password = bcrypt($request->password);
        }
        $user->save();
        return redirect()->route('admin.users.index')->with('success', 'Data user berhasil diupdate.');
    }

    // METHOD BARU UNTUK HAPUS
    public function destroy(User $user)
    {
        if (auth()->id() == $user->id) {
            return redirect()->route('admin.users.index')
                             ->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }
        $user->delete();
        return redirect()->route('admin.users.index')
                         ->with('success', 'User berhasil dihapus.');
    }
}