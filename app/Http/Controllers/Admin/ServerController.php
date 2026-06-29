<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Node;
use App\Models\Server;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServerController extends Controller
{
    private const STATUSES = ['offline', 'starting', 'running', 'stopping'];

    public function index(): View
    {
        $servers = Server::with(['node', 'owner'])->latest()->paginate(15);

        return view('admin.servers.index', compact('servers'));
    }

    public function create(): View
    {
        return view('admin.servers.create', [
            'server' => new Server(['status' => 'offline', 'memory_mb' => 1024, 'disk_mb' => 5120]),
        ] + $this->formData());
    }

    public function store(Request $request): RedirectResponse
    {
        Server::create($this->validated($request));

        return redirect()->route('admin.servers.index')->with('status', 'Server created.');
    }

    public function edit(Server $server): View
    {
        return view('admin.servers.edit', compact('server') + $this->formData());
    }

    public function update(Request $request, Server $server): RedirectResponse
    {
        $server->update($this->validated($request));

        return redirect()->route('admin.servers.index')->with('status', 'Server updated.');
    }

    public function destroy(Server $server): RedirectResponse
    {
        $server->delete();

        return redirect()->route('admin.servers.index')->with('status', 'Server deleted.');
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
            'statuses' => self::STATUSES,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'node_id' => ['required', 'exists:nodes,id'],
            'owner_id' => ['required', 'exists:users,id'],
            'status' => ['required', 'in:'.implode(',', self::STATUSES)],
            'memory_mb' => ['required', 'integer', 'min:0'],
            'disk_mb' => ['required', 'integer', 'min:0'],
            'port' => ['nullable', 'integer', 'min:1', 'max:65535'],
        ]);
    }
}
