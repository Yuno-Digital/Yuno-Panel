<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Egg;
use App\Models\Node;
use App\Models\Server;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ServerController extends Controller
{
    private const STATUSES = ['offline', 'starting', 'running', 'stopping'];

    public function index(): View
    {
        $servers = Server::with(['node', 'owner', 'egg'])->latest()->paginate(15);

        return view('admin.servers.index', compact('servers'));
    }

    public function create(): View
    {
        return view('admin.servers.create', [
            'server' => new Server(['status' => 'offline', 'memory_mb' => 1024, 'disk_mb' => 5120, 'cpu' => 0, 'swap_mb' => 0]),
        ] + $this->formData());
    }

    public function store(Request $request): RedirectResponse
    {
        $egg = Egg::with('variables')->findOrFail($request->input('egg_id'));
        $data = $this->validated($request, $egg);

        $server = DB::transaction(function () use ($data, $egg, $request) {
            $server = Server::create($data);
            $this->syncVariables($server, $egg, $request->input('variables', []));

            return $server;
        });

        return redirect()->route('admin.servers.edit', $server)->with('status', 'Server created.');
    }

    public function edit(Server $server): View
    {
        $server->load('variables.eggVariable');

        return view('admin.servers.edit', compact('server') + $this->formData());
    }

    public function update(Request $request, Server $server): RedirectResponse
    {
        $egg = Egg::with('variables')->findOrFail($request->input('egg_id'));
        $data = $this->validated($request, $egg);

        DB::transaction(function () use ($server, $data, $egg, $request) {
            $server->update($data);
            $this->syncVariables($server, $egg, $request->input('variables', []));
        });

        return redirect()->route('admin.servers.edit', $server)->with('status', 'Server updated.');
    }

    public function destroy(Server $server): RedirectResponse
    {
        $server->delete();

        return redirect()->route('admin.servers.index')->with('status', 'Server deleted.');
    }

    /**
     * Persist the server's variable values, one row per egg variable.
     *
     * @param  array<string, mixed>  $values  keyed by env variable name
     */
    private function syncVariables(Server $server, Egg $egg, array $values): void
    {
        foreach ($egg->variables as $variable) {
            $server->variables()->updateOrCreate(
                ['egg_variable_id' => $variable->id],
                ['variable_value' => $values[$variable->env_variable] ?? $variable->default_value],
            );
        }
    }

    /**
     * Shared data for the create/edit forms.
     *
     * @return array<string, mixed>
     */
    private function formData(): array
    {
        return [
            'nodes' => Node::orderBy('name')->get(),
            'users' => User::orderBy('name')->get(),
            'eggs' => Egg::with('variables')->orderBy('name')->get(),
            'statuses' => self::STATUSES,
        ];
    }

    /**
     * Validate the form, including the egg's docker images and variable rules.
     *
     * @return array<string, mixed>
     */
    private function validated(Request $request, Egg $egg): array
    {
        $images = array_values($egg->docker_images ?? []);

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'node_id' => ['required', 'exists:nodes,id'],
            'owner_id' => ['required', 'exists:users,id'],
            'egg_id' => ['required', 'exists:eggs,id'],
            'docker_image' => ['required', 'string', $images ? Rule::in($images) : 'string'],
            'startup' => ['required', 'string'],
            'status' => ['required', 'in:'.implode(',', self::STATUSES)],
            'memory_mb' => ['required', 'integer', 'min:0'],
            'disk_mb' => ['required', 'integer', 'min:0'],
            'cpu' => ['required', 'integer', 'min:0'],
            'swap_mb' => ['required', 'integer', 'min:0'],
            'port' => ['nullable', 'integer', 'min:1', 'max:65535'],
        ];

        // Apply each egg variable's own validation rules to its submitted value.
        foreach ($egg->variables as $variable) {
            $rules["variables.{$variable->env_variable}"] = $variable->rules ?: 'nullable|string';
        }

        $validated = $request->validate($rules);

        unset($validated['variables']);

        return $validated;
    }
}
