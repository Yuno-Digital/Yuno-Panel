<?php

namespace App\Http\Controllers;

use App\Models\Server;
use App\Services\WingsClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ServerController extends Controller
{
    public function __construct(private readonly WingsClient $wings) {}

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
     * (Re)install the server's container on its node.
     */
    public function install(Request $request, Server $server): RedirectResponse
    {
        $this->authorizeServer($request, $server);
        $server->load(['node', 'allocation', 'variables.eggVariable']);

        $ok = $this->wings->createContainer($server);

        return back()->with($ok ? 'status' : 'error',
            $ok ? __('Server installed on the node.') : __('Could not reach the node daemon.'));
    }

    /**
     * Send a power action to the daemon.
     */
    public function power(Request $request, Server $server): RedirectResponse
    {
        $this->authorizeServer($request, $server);
        $data = $request->validate(['action' => ['required', 'in:start,stop,restart']]);

        $ok = $this->wings->power($server->load('node'), $data['action']);

        return back()->with($ok ? 'status' : 'error',
            $ok ? __('Power action sent: :a', ['a' => $data['action']]) : __('Could not reach the node daemon.'));
    }

    /**
     * Live resource stats (JSON, polled by the console page).
     */
    public function stats(Request $request, Server $server): JsonResponse
    {
        $this->authorizeServer($request, $server);

        return response()->json($this->wings->stats($server->load('node')) ?? ['state' => 'unreachable']);
    }

    /**
     * Console log tail (JSON).
     */
    public function logs(Request $request, Server $server): JsonResponse
    {
        $this->authorizeServer($request, $server);

        return response()->json(['logs' => $this->wings->logs($server->load('node'))]);
    }

    /**
     * List files in the server's volume (JSON).
     */
    public function files(Request $request, Server $server): JsonResponse
    {
        $this->authorizeServer($request, $server);
        $path = (string) $request->query('path', '/');

        return response()->json(['path' => $path, 'entries' => $this->wings->files($server->load('node'), $path)]);
    }

    /**
     * Read a file (JSON).
     */
    public function fileRead(Request $request, Server $server): JsonResponse
    {
        $this->authorizeServer($request, $server);
        $path = (string) $request->query('path', '');

        return response()->json(['path' => $path, 'contents' => $this->wings->fileContents($server->load('node'), $path)]);
    }

    /**
     * Write a file.
     */
    public function fileWrite(Request $request, Server $server): JsonResponse
    {
        $this->authorizeServer($request, $server);
        $data = $request->validate([
            'path' => ['required', 'string'],
            'contents' => ['nullable', 'string'],
        ]);

        $ok = $this->wings->writeFile($server->load('node'), $data['path'], $data['contents'] ?? '');

        return response()->json(['saved' => $ok], $ok ? 200 : 502);
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
