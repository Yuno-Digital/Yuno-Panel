<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RoleController extends Controller
{
    public function index(): View
    {
        return view('admin.roles.index', [
            'roles' => Role::withCount('users')->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.roles.create', ['role' => new Role]);
    }

    public function store(Request $request): RedirectResponse
    {
        Role::create($this->validated($request));

        return redirect()->route('admin.roles.index')->with('status', 'Role created.');
    }

    public function edit(Role $role): View
    {
        return view('admin.roles.edit', compact('role'));
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $data = $this->validated($request, $role);

        // The default Admin role always keeps full access.
        if ($role->is_default) {
            $data['permissions'] = array_values(array_unique(array_merge($data['permissions'], ['administrator'])));
        }

        $role->update($data);

        return redirect()->route('admin.roles.index')->with('status', 'Role updated.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        if ($role->is_default) {
            return redirect()->route('admin.roles.index')
                ->with('error', 'The default Admin role cannot be deleted.');
        }

        $role->delete();

        return redirect()->route('admin.roles.index')->with('status', 'Role deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?Role $role = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('roles', 'name')->ignore($role?->id)],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => [Rule::in(array_keys(Role::PERMISSIONS))],
        ]);

        return [
            'name' => $data['name'],
            'permissions' => array_values($data['permissions'] ?? []),
        ];
    }
}
