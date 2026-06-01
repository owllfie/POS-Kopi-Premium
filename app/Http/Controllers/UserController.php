<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    private function getActiveUser()
    {
        if (session()->has('simulated_user_id')) {
            return User::find(session('simulated_user_id'));
        }
        return Auth::user();
    }

    public function index(Request $request)
    {
        $viewTrash = $request->input('trash', '0') === '1';

        // Load internal users, excluding superadmins (id_role = 1)
        $query = User::where('id_role', '!=', 1)->with('role');

        if ($viewTrash) {
            $query->onlyTrashed();
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();
        
        // Exclude superadmin role from assignments dropdown
        $roles = Role::where('id_role', '!=', 1)->get();

        return view('users.index', compact('users', 'roles', 'viewTrash'));
    }

    public function store(Request $request)
    {
        $admin = $this->getActiveUser();
        $validated = $request->validate([
            'username' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'id_role' => 'required|exists:role,id_role',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

        return back()->with('success', 'User berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $admin = $this->getActiveUser();
        $user = User::findOrFail($id);

        // Security check: cannot modify superadmins
        if ($user->id_role == 1) {
            return back()->with('error', 'Tidak dapat mengubah akun Superadmin.');
        }

        $validated = $request->validate([
            'username' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id_user . ',id_user',
            'password' => 'nullable|string|min:6',
            'id_role' => 'required|exists:role,id_role',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return back()->with('success', 'User berhasil diperbarui.');
    }

    public function delete(Request $request, $id)
    {
        $admin = $this->getActiveUser();
        $user = User::findOrFail($id);

        if ($user->id_role == 1) {
            return back()->with('error', 'Tidak dapat menghapus akun Superadmin.');
        }

        $user->delete();

        return back()->with('success', 'User dinonaktifkan (dipindahkan ke Trash).');
    }

    public function restore(Request $request, $id)
    {
        $admin = $this->getActiveUser();
        $user = User::onlyTrashed()->findOrFail($id);
        $user->restore();

        return back()->with('success', 'User berhasil diaktifkan kembali.');
    }

    public function forceDelete(Request $request, $id)
    {
        $admin = $this->getActiveUser();
        $user = User::onlyTrashed()->findOrFail($id);
        $user->forceDelete();

        return back()->with('success', 'User berhasil dihapus secara permanen.');
    }
}
