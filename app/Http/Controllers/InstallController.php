<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Support\Installer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class InstallController extends Controller
{
    /**
     * Show the installer, or send finished installs away.
     */
    public function show(): View|RedirectResponse
    {
        if (Installer::isInstalled()) {
            return redirect()->route('login');
        }

        return view('install.index', [
            'requirements' => Installer::requirements(),
            'met' => Installer::requirementsMet(),
        ]);
    }

    /**
     * Run migrations, create the admin account and mark the panel installed.
     */
    public function store(Request $request): RedirectResponse
    {
        if (Installer::isInstalled()) {
            return redirect()->route('login');
        }

        if (! Installer::requirementsMet()) {
            return back()->with('error', __('Please resolve the failed requirements first.'));
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        // Make sure a SQLite database file exists before migrating.
        if (config('database.default') === 'sqlite') {
            $path = config('database.connections.sqlite.database');
            if (is_string($path) && $path !== ':memory:' && ! is_file($path)) {
                @touch($path);
            }
        }

        if (empty(config('app.key'))) {
            Artisan::call('key:generate', ['--force' => true]);
        }

        Artisan::call('migrate', ['--force' => true]);

        // The single, protected default Admin role.
        $role = Role::firstOrCreate(['name' => 'Admin'], ['permissions' => ['administrator'], 'is_default' => true]);
        $role->update(['permissions' => ['administrator'], 'is_default' => true]);

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'is_admin' => true,
            'role_id' => $role->id,
        ]);

        Installer::markInstalled();

        return redirect()->route('login')->with('status', __('Installation complete — please sign in.'));
    }
}
