<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        $users = User::with('role')->withCount(['servers', 'serverSubusers'])->latest()->paginate(15);

        return view('admin.users.index', compact('users'));
    }

    public function create(): View
    {
        return view('admin.users.create', ['user' => new User, 'roles' => Role::orderBy('name')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'is_admin' => ['nullable', 'boolean'],
            'role_id' => ['nullable', Rule::exists('roles', 'id')],
        ]);

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'is_admin' => $request->boolean('is_admin'),
            'role_id' => $data['role_id'] ?? null,
        ]);

        return redirect()->route('admin.users.index')->with('status', 'User created.');
    }

    public function edit(User $user): View|RedirectResponse
    {
        if ($user->is_root && ! $user->is(request()->user())) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Only the main admin can edit that account.');
        }

        return view('admin.users.edit', compact('user') + ['roles' => Role::orderBy('name')->get()]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        // Only the main admin may edit the main admin account.
        if ($user->is_root && ! $user->is($request->user())) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Only the main admin can edit that account.');
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8'],
            'is_admin' => ['nullable', 'boolean'],
            'role_id' => ['nullable', Rule::exists('roles', 'id')],
        ]);

        $user->name = $data['name'];
        $user->email = $data['email'];
        // The main admin always keeps administrator access — it can't be removed.
        $user->is_admin = $user->is_root ? true : $request->boolean('is_admin');
        $user->role_id = $data['role_id'] ?? null;

        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        return redirect()->route('admin.users.index')->with('status', 'User updated.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->is_root) {
            return redirect()->route('admin.users.index')
                ->with('error', 'The main admin account cannot be deleted.');
        }

        if ($user->is($request->user())) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('status', 'User deleted.');
    }
}
