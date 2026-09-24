<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $roleId = $request->input('role_id');
        $status = $request->input('status');

        $users = User::query()
            ->with('role')
            ->when($search, function ($q) use ($search) {
                $q->where(function ($w) use ($search) {
                    $w->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when($roleId, fn ($q) => $q->where('role_id', $roleId))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $roles = Role::orderBy('name')->get();

        return view('admin.users.index', compact('users', 'roles', 'search', 'roleId', 'status'));
    }

    public function create()
    {
        $roles = Role::whereIn('name', ['admin', 'staff'])->orderBy('name')->get();
        return view('admin.users.create', compact('roles'));
    }

    public function store(StoreUserRequest $request)
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);

        User::create($data);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    public function show(User $user)
    {
        return redirect()->route('admin.users.index');
    }

    public function edit(User $user)
    {
        $roles = Role::orderBy('name')->get();
        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $data = $request->validated();

        // Prevent self-lockout: cannot change own role or deactivate self
        if ($user->id === Auth::id()) {
            if ((int) $data['role_id'] !== (int) $user->role_id) {
                return back()->with('error', 'You cannot change your own role.');
            }
            if ($data['status'] !== 'active') {
                return back()->with('error', 'You cannot deactivate your own account.');
            }
        }

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        // Cannot delete self
        if ($user->id === Auth::id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        // Cannot delete a user with audit history
        $hasHistory =
            $user->sales()->exists() ||
            $user->purchases()->exists() ||
            $user->stockMovements()->exists() ||
            $user->expenses()->exists() ||
            $user->payments()->exists();

        if ($hasHistory) {
            return back()->with('error', 'Cannot delete this user: they have activity history. Deactivate them instead.');
        }

        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }
}