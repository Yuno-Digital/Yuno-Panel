<?php

namespace App\Http\Controllers;

use App\Models\Server;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ServerController extends Controller
{
    /**
     * List the servers visible to the current user.
     * Admins see every server; regular users only their own.
     */
    public function index(): View
    {
        $user = Auth::user();

        $servers = Server::with(['node', 'owner', 'egg', 'allocation'])
            ->when(! $user->is_admin, fn ($query) => $query->where('owner_id', $user->id))
            ->latest()
            ->get();

        return view('servers.index', compact('servers'));
    }

    /**
     * Show the management page for a single server the user may access.
     */
    public function show(Request $request, Server $server): View
    {
        $this->authorizeServer($request, $server);

        $server->load(['egg.variables', 'node', 'allocation', 'variables.eggVariable']);

        return view('servers.show', compact('server'));
    }

    /**
     * Let the owner update the values of user-editable egg variables.
     */
    public function update(Request $request, Server $server): RedirectResponse
    {
        $this->authorizeServer($request, $server);

        $editable = $server->egg
            ? $server->egg->variables->where('user_editable', true)
            : collect();

        $rules = [];
        foreach ($editable as $variable) {
            $rules["variables.{$variable->env_variable}"] = $variable->rules ?: 'nullable|string';
        }
        $request->validate($rules);

        foreach ($editable as $variable) {
            $server->variables()->updateOrCreate(
                ['egg_variable_id' => $variable->id],
                ['variable_value' => $request->input("variables.{$variable->env_variable}", $variable->default_value)],
            );
        }

        return redirect()->route('servers.show', $server)->with('status', 'Settings saved.');
    }

    /**
     * Owners may manage their own servers; admins may manage any.
     */
    private function authorizeServer(Request $request, Server $server): void
    {
        $user = $request->user();

        abort_unless($user->is_admin || $server->owner_id === $user->id, 403);
    }
}
