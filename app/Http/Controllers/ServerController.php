<?php

namespace App\Http\Controllers;

use App\Models\ScheduledTask;
use App\Models\Server;
use App\Models\ServerActivity;
use App\Models\ServerWebhook;
use App\Models\User;
use App\Notifications\PanelNotification;
use App\Services\WingsClient;
use App\Support\Webhooks;
use Cron\CronExpression;
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
            ->when(! $user->isAdmin(), fn ($query) => $query->where(function ($q) use ($user) {
                $q->where('owner_id', $user->id)
                    ->orWhereHas('subusers', fn ($s) => $s->whereKey($user->id));
            }))
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

        $server->load(['egg.variables', 'node', 'allocation', 'variables.eggVariable', 'subusers',
            'scheduledTasks' => fn ($q) => $q->orderBy('name'),
            'activities' => fn ($q) => $q->with('user')->limit(100),
            'webhooks' => fn ($q) => $q->latest()]);

        // What the current user may do here (owner/admin can do everything).
        $user = $request->user();
        $manages = $user->isAdmin() || $server->owner_id === $user->id;
        $permissions = $manages
            ? array_keys(Server::SUBUSER_PERMISSIONS)
            : ($server->subuserPermissions($user) ?? []);

        // Tabs the user may see: network/settings are always shown, subusers and
        // webhooks are owner/admin only, the rest need the matching permission.
        $tabs = array_values(array_filter(
            ['console', 'files', 'schedules', 'activity', 'network', 'startup', 'subusers', 'webhooks', 'settings'],
            fn ($t) => match ($t) {
                'settings', 'network' => true,
                'subusers', 'webhooks' => $manages,
                default => in_array($t, $permissions, true),
            },
        ));
        $activeTab = in_array($tab, $tabs, true) ? $tab : ($tabs[0] ?? 'settings');

        return view('servers.show', compact('server', 'activeTab', 'manages', 'permissions', 'tabs'));
    }

    /**
     * Let the owner update the startup command, docker image and the values of
     * the user-editable egg variables.
     */
    public function update(Request $request, Server $server): RedirectResponse
    {
        $this->authorizeServer($request, $server, 'startup');

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

        ServerActivity::record($server, 'server:settings');

        return redirect()->route('servers.show', $server)->with('status', 'Startup settings saved.');
    }

    /**
     * (Re)install the server's container on its node.
     */
    public function install(Request $request, Server $server): RedirectResponse
    {
        $this->authorizeServer($request, $server, 'reinstall');
        $server->load(['node', 'egg', 'allocation', 'variables.eggVariable']);

        $ok = $this->wings->createContainer($server);

        if ($ok) {
            ServerActivity::record($server, 'server:reinstall');
            $server->owner?->notify(new PanelNotification(
                __('Installation started'),
                __('":name" is being (re)installed.', ['name' => $server->name]),
                route('servers.show', $server),
            ));
            Webhooks::dispatch('server.reinstall', [
                'server' => ['id' => $server->id, 'uuid' => $server->uuid, 'name' => $server->name],
            ], $server);
        }

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
        $this->authorizeServer($request, $server, 'power');
        $data = $request->validate(['action' => ['required', 'in:start,stop,restart']]);

        $ok = $this->wings->power($server->load('node'), $data['action']);

        if ($ok) {
            ServerActivity::record($server, 'server:power', ['action' => $data['action']]);
            Webhooks::dispatch('server.power', [
                'server' => ['id' => $server->id, 'uuid' => $server->uuid, 'name' => $server->name],
                'action' => $data['action'],
            ], $server);
        }

        return back()->with($ok ? 'status' : 'error',
            $ok ? __('Power action sent: :a', ['a' => $data['action']]) : __('Could not reach the node daemon.'));
    }

    /**
     * Send a console command to the running server.
     */
    public function command(Request $request, Server $server): JsonResponse
    {
        $this->authorizeServer($request, $server, 'console');
        $data = $request->validate(['command' => ['required', 'string', 'max:2000']]);

        $ok = $this->wings->command($server->load('node'), $data['command']);

        if ($ok) {
            ServerActivity::record($server, 'server:command', ['command' => $data['command']]);
        }

        return response()->json(['sent' => $ok], $ok ? 200 : 502);
    }

    /**
     * Issue the console WebSocket connection details (daemon ws URL + a
     * short-lived signed token) for the browser to connect directly to the node.
     */
    public function websocket(Request $request, Server $server): JsonResponse
    {
        $this->authorizeServer($request, $server, 'console');

        return response()->json($this->wings->websocket($server->load('node')));
    }

    /**
     * Live resource stats (JSON, polled by the console page).
     */
    public function stats(Request $request, Server $server): JsonResponse
    {
        $this->authorizeServer($request, $server, 'console');

        return response()->json($this->wings->stats($server->load('node')) ?? ['state' => 'unreachable']);
    }

    /**
     * Console log tail (JSON).
     */
    public function logs(Request $request, Server $server): JsonResponse
    {
        $this->authorizeServer($request, $server, 'console');

        return response()->json(['logs' => $this->wings->logs($server->load('node'))]);
    }

    /**
     * List files in the server's volume (JSON).
     */
    public function files(Request $request, Server $server): JsonResponse
    {
        $this->authorizeServer($request, $server, 'files');
        $path = (string) $request->query('path', '/');

        return response()->json(['path' => $path, 'entries' => $this->wings->files($server->load('node'), $path)]);
    }

    /**
     * Read a file (JSON).
     */
    public function fileRead(Request $request, Server $server): JsonResponse
    {
        $this->authorizeServer($request, $server, 'files');
        $path = (string) $request->query('path', '');

        return response()->json(['path' => $path, 'contents' => $this->wings->fileContents($server->load('node'), $path)]);
    }

    /**
     * Write a file.
     */
    public function fileWrite(Request $request, Server $server): JsonResponse
    {
        $this->authorizeServer($request, $server, 'files');
        $data = $request->validate([
            'path' => ['required', 'string'],
            'contents' => ['nullable', 'string'],
        ]);

        $ok = $this->wings->writeFile($server->load('node'), $data['path'], $data['contents'] ?? '');

        if ($ok) {
            ServerActivity::record($server, 'file:write', ['path' => $data['path']]);
        }

        return response()->json(['saved' => $ok], $ok ? 200 : 502);
    }

    /**
     * Delete one or more files/directories.
     */
    public function fileDelete(Request $request, Server $server): JsonResponse
    {
        $this->authorizeServer($request, $server, 'files');
        $data = $request->validate([
            'paths' => ['required', 'array', 'min:1'],
            'paths.*' => ['required', 'string'],
        ]);

        $ok = $this->wings->deleteFiles($server->load('node'), $data['paths']);

        if ($ok) {
            ServerActivity::record($server, 'file:delete', ['count' => count($data['paths'])]);
        }

        return response()->json(['deleted' => $ok], $ok ? 200 : 502);
    }

    /**
     * Grant (or update) a subuser's access to the server.
     */
    public function storeSubuser(Request $request, Server $server): RedirectResponse
    {
        $this->authorizeManage($request, $server);

        $data = $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => [Rule::in(array_keys(Server::SUBUSER_PERMISSIONS))],
        ]);

        $user = User::where('email', $data['email'])->firstOrFail();

        if ($user->id === $server->owner_id) {
            return back()->with('error', __('The owner already has full access.'));
        }

        $permissions = array_values($data['permissions'] ?? []);
        $server->subusers()->syncWithoutDetaching([$user->id]);
        $server->subusers()->updateExistingPivot($user->id, ['permissions' => $permissions]);

        ServerActivity::record($server, 'subuser:updated', ['user' => $user->email]);

        $user->notify(new PanelNotification(
            __('Server access granted'),
            __('You were given access to ":name".', ['name' => $server->name]),
            route('servers.show', $server),
        ));

        return back()->with('status', __('Subuser saved.'));
    }

    /**
     * Remove a subuser's access.
     */
    public function destroySubuser(Request $request, Server $server, User $user): RedirectResponse
    {
        $this->authorizeManage($request, $server);

        $server->subusers()->detach($user->id);
        ServerActivity::record($server, 'subuser:removed', ['user' => $user->email]);

        return back()->with('status', __('Subuser removed.'));
    }

    /**
     * Cron expression for each schedule preset (or a validated custom one).
     */
    private const SCHEDULE_PRESETS = [
        'every_5' => '*/5 * * * *',
        'every_15' => '*/15 * * * *',
        'every_30' => '*/30 * * * *',
        'hourly' => '0 * * * *',
        'every_6h' => '0 */6 * * *',
        'daily' => '0 0 * * *',
        'weekly' => '0 0 * * 0',
    ];

    /**
     * Create a scheduled task (a timed power action or console command).
     */
    public function storeSchedule(Request $request, Server $server): RedirectResponse
    {
        $this->authorizeServer($request, $server, 'schedules');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'action' => ['required', 'in:power,command'],
            'power_action' => ['nullable', 'required_if:action,power', 'in:start,stop,restart'],
            'command' => ['nullable', 'required_if:action,command', 'string', 'max:2000'],
            'preset' => ['required', Rule::in([...array_keys(self::SCHEDULE_PRESETS), 'custom'])],
            'cron' => ['nullable', 'required_if:preset,custom', 'string', 'max:100'],
        ]);

        $cron = $data['preset'] === 'custom' ? trim((string) $data['cron']) : self::SCHEDULE_PRESETS[$data['preset']];
        if (! CronExpression::isValidExpression($cron)) {
            return back()->withInput()->with('error', __('That cron expression is not valid.'));
        }

        $task = new ScheduledTask([
            'name' => $data['name'],
            'action' => $data['action'],
            'payload' => $data['action'] === 'power' ? $data['power_action'] : $data['command'],
            'cron' => $cron,
            'is_active' => true,
        ]);
        $server->scheduledTasks()->save($task);
        $task->forceFill(['next_run_at' => $task->computeNextRun()])->save();
        ServerActivity::record($server, 'schedule:created', ['name' => $task->name]);

        return redirect()->route('servers.show.tab', [$server, 'schedules'])->with('status', __('Schedule created.'));
    }

    /**
     * Enable or disable a scheduled task.
     */
    public function toggleSchedule(Request $request, Server $server, ScheduledTask $schedule): RedirectResponse
    {
        $this->authorizeServer($request, $server, 'schedules');
        abort_if($schedule->server_id !== $server->id, 404);

        $active = ! $schedule->is_active;
        $schedule->forceFill([
            'is_active' => $active,
            'next_run_at' => $active ? $schedule->computeNextRun() : null,
        ])->save();
        ServerActivity::record($server, 'schedule:toggled', ['name' => $schedule->name, 'active' => $active]);

        return redirect()->route('servers.show.tab', [$server, 'schedules'])
            ->with('status', $active ? __('Schedule enabled.') : __('Schedule disabled.'));
    }

    /**
     * Delete a scheduled task.
     */
    public function destroySchedule(Request $request, Server $server, ScheduledTask $schedule): RedirectResponse
    {
        $this->authorizeServer($request, $server, 'schedules');
        abort_if($schedule->server_id !== $server->id, 404);

        $name = $schedule->name;
        $schedule->delete();
        ServerActivity::record($server, 'schedule:deleted', ['name' => $name]);

        return redirect()->route('servers.show.tab', [$server, 'schedules'])->with('status', __('Schedule deleted.'));
    }

    /**
     * Add a webhook endpoint to the server.
     */
    public function storeWebhook(Request $request, Server $server): RedirectResponse
    {
        $this->authorizeManage($request, $server);

        $data = $request->validate([
            'url' => ['required', 'url', 'max:2048'],
            'events' => ['nullable', 'array'],
            'events.*' => [Rule::in(array_keys(ServerWebhook::EVENTS))],
        ]);

        $server->webhooks()->create([
            'url' => $data['url'],
            'events' => array_values($data['events'] ?? []),
            'is_active' => true,
        ]);

        return redirect()->route('servers.show.tab', [$server, 'webhooks'])->with('status', __('Webhook added.'));
    }

    /**
     * Enable or disable a webhook.
     */
    public function toggleWebhook(Request $request, Server $server, ServerWebhook $webhook): RedirectResponse
    {
        $this->authorizeManage($request, $server);
        abort_if($webhook->server_id !== $server->id, 404);

        $webhook->update(['is_active' => ! $webhook->is_active]);

        return redirect()->route('servers.show.tab', [$server, 'webhooks'])->with('status', __('Webhook updated.'));
    }

    /**
     * Delete a webhook.
     */
    public function destroyWebhook(Request $request, Server $server, ServerWebhook $webhook): RedirectResponse
    {
        $this->authorizeManage($request, $server);
        abort_if($webhook->server_id !== $server->id, 404);

        $webhook->delete();

        return redirect()->route('servers.show.tab', [$server, 'webhooks'])->with('status', __('Webhook deleted.'));
    }

    /**
     * Authorize access to a server. Owners and admins may do anything; subusers
     * need to be granted the specific permission (null = any subuser access).
     */
    private function authorizeServer(Request $request, Server $server, ?string $permission = null): void
    {
        $user = $request->user();

        if ($user->isAdmin() || $server->owner_id === $user->id) {
            return;
        }

        $permissions = $server->subuserPermissions($user);

        abort_if($permissions === null, 403);
        abort_if($permission !== null && ! in_array($permission, $permissions, true), 403);
    }

    /**
     * Only the owner or an admin may manage a server's subusers.
     */
    private function authorizeManage(Request $request, Server $server): void
    {
        $user = $request->user();

        abort_unless($user->isAdmin() || $server->owner_id === $user->id, 403);
    }
}
