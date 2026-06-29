<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Node;
use App\Services\WingsClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NodeController extends Controller
{
    public function __construct(private readonly WingsClient $wings)
    {
    }

    public function index(): View
    {
        $nodes = Node::withCount('servers')->latest()->paginate(15);

        return view('admin.nodes.index', compact('nodes'));
    }

    public function create(): View
    {
        return view('admin.nodes.create', ['node' => new Node()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $node = Node::create($this->validated($request));

        return redirect()->route('admin.nodes.index')
            ->with($this->detect($node));
    }

    public function edit(Node $node): View
    {
        return view('admin.nodes.edit', compact('node'));
    }

    public function update(Request $request, Node $node): RedirectResponse
    {
        $node->update($this->validated($request));

        return redirect()->route('admin.nodes.index')
            ->with($this->detect($node));
    }

    public function destroy(Node $node): RedirectResponse
    {
        $node->delete();

        return redirect()->route('admin.nodes.index')->with('status', 'Node deleted.');
    }

    /**
     * Contact the node's daemon to auto-detect memory/disk and online status.
     * Returns the flash payload describing the outcome.
     *
     * @return array<string, string>
     */
    private function detect(Node $node): array
    {
        $system = $this->wings->system($node);

        if ($system === null) {
            $node->forceFill(['is_online' => false])->save();

            return ['error' => "Saved, but the daemon at {$node->fqdn}:{$node->daemon_port} could not be reached. Check the FQDN, port and token."];
        }

        $node->forceFill([
            'is_online' => true,
            'memory_mb' => (int) ($system['memory_mb'] ?? 0),
            'disk_mb' => (int) ($system['disk_mb'] ?? 0),
        ])->save();

        $memGb = round($node->memory_mb / 1024, 1);
        $diskGb = round($node->disk_mb / 1024, 1);

        return ['status' => "Node online. Detected {$memGb} GB RAM and {$diskGb} GB disk."];
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'fqdn' => ['required', 'string', 'max:255'],
            'daemon_port' => ['required', 'integer', 'min:1', 'max:65535'],
            'daemon_token' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);
    }
}
