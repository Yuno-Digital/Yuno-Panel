<?php

namespace App\Http\Controllers;

use App\Models\Server;
use App\Services\WingsClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
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
    public function show(Request $request, Server $server, ?string $tab = null): View
    {
        $this->authorizeServer($request, $server);

        $server->load(['egg.variables', 'node', 'allocation', 'variables.eggVariable']);

        $tabs = ['console', 'files', 'startup', 'settings'];
        $activeTab = in_array($tab, $tabs, true) ? $tab : 'console';

        return view('servers.show', compact('server', 'activeTab'));
    }

    /**
     * Let the owner update the startup command, docker image and the values of
     * the user-editable egg variables.
     */
    public function update(Request $request, Server $server): RedirectResponse
    {
        $this->authorizeServer($request, $server);

        $egg = $server->egg;

        $editable = $egg
            ? $egg->variables->where('user_editable', true)
            : collect();

        // Allowed docker images and startup commands come from the egg; the
        // server's current values are always allowed so they stay selectable.
        $images = collect($egg?->docker_images ?? [])->values()
            ->push($server->docker_image)->filter()->unique()->values()->all();

        $commands = collect($egg?->startup_commands ?? [])
            ->map(fn ($c) => is_array($c) ? ($c['command'] ?? '') : (string) $c)
            ->push($server->startup)->filter()->unique()->values()->all();

        $rules = [
            'docker_image' => ['nullable', 'string', $images ? Rule::in($images) : 'string'],
            'startup' => ['nullable', 'string', $commands ? Rule::in($commands) : 'string'],
        ];
        foreach ($editable as $variable) {
            $rules["variables.{$variable->env_variable}"] = $variable->rules ?: 'nullable|string';
        }
        $validated = $request->validate($rules);

        if (! empty($validated['docker_image'])) {
            $server->docker_image = $validated['docker_image'];
        }
        if (! empty($validated['startup'])) {
            $server->startup = $validated['startup'];
        }
        $server->save();

        foreach ($editable as $variable) {
            $server->variables()->updateOrCreate(
                ['egg_variable_id' => $variable->id],
                ['variable_value' => $request->input("variables.{$variable->env_variable}", $variable->default_value)],
            );
        }

        return redirect()->route('servers.show', $server)->with('status', 'Startup settings saved.');
    }

    /**
     * (Re)install the server's container on its node.
     */
    public function install(Request $request, Server $server): RedirectResponse
    {
        $this->authorizeServer($request, $server);
        $server->load(['node', 'egg', 'allocation', 'variables.eggVariable']);

        $ok = $this->wings->createContainer($server);

        return back()->with($ok ? 'status' : 'error',
            $ok
                ? __('Installation started — watch the progress in the console.')
                : __('Could not reach the node daemon.'));
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
     * Send a console command to the running server.
     */
    public function command(Request $request, Server $server): JsonResponse
    {
        $this->authorizeServer($request, $server);
        $data = $request->validate(['command' => ['required', 'string', 'max:2000']]);

        $ok = $this->wings->command($server->load('node'), $data['command']);

        return response()->json(['sent' => $ok], $ok ? 200 : 502);
    }

    /**
     * Issue the console WebSocket connection details (daemon ws URL + a
     * short-lived signed token) for the browser to connect directly to the node.
     */
    public function websocket(Request $request, Server $server): JsonResponse
    {
        $this->authorizeServer($request, $server);

        return response()->json($this->wings->websocket($server->load('node')));
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
     * Delete one or more files/directories.
     */
    public function fileDelete(Request $request, Server $server): JsonResponse
    {
        $this->authorizeServer($request, $server);
        $data = $request->validate([
            'paths' => ['required', 'array', 'min:1'],
            'paths.*' => ['required', 'string'],
        ]);

        $ok = $this->wings->deleteFiles($server->load('node'), $data['paths']);

        return response()->json(['deleted' => $ok], $ok ? 200 : 502);
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
