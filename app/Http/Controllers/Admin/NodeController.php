<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Node;
use App\Services\WingsClient;
use App\Services\WingsUpdateChecker;
use App\Support\Format;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NodeController extends Controller
{
    public function __construct(
        private readonly WingsClient $wings,
        private readonly WingsUpdateChecker $wingsUpdates,
    ) {}

    public function index(): View
    {
        $nodes = Node::withCount('servers')->latest()->paginate(15);

        return view('admin.nodes.index', compact('nodes'));
    }

    public function create(): View
    {
        return view('admin.nodes.create', ['node' => new Node]);
    }

    public function store(Request $request): RedirectResponse
    {
        $node = Node::create($this->validated($request) + [
            'daemon_token' => Node::generateToken(),
        ]);

        return redirect()->route('admin.nodes.edit', $node)
            ->with('status', __('Node created. Configure the daemon using the Auto Deploy tab.'));
    }

    /**
     * Generate a fresh daemon token (invalidates the old one).
     */
    public function regenerateToken(Node $node): RedirectResponse
    {
        $node->update(['daemon_token' => Node::generateToken()]);

        return redirect()->route('admin.nodes.edit', $node)
            ->with('status', __('New token generated — reconfigure the daemon.'));
    }

    public function edit(Node $node): View
    {
        $node->load(['allocations' => fn ($q) => $q->orderBy('ip')->orderBy('port'), 'allocations.server']);

        return view('admin.nodes.edit', [
            'node' => $node,
            'latestDaemon' => $this->wingsUpdates->latestVersion(),
            'daemonUpdateAvailable' => $this->wingsUpdates->updateAvailable($node->daemon_version),
        ]);
    }

    /**
     * Trigger a self-update on the node's Wings daemon.
     */
    public function upgrade(Node $node): RedirectResponse
    {
        if (! $this->wings->update($node)) {
            return redirect()->route('admin.nodes.edit', $node)
                ->with('error', __('Could not reach the daemon to update it. Is the node online?'));
        }

        return redirect()->route('admin.nodes.edit', $node)
            ->with('status', __('Daemon update started — it will pull the latest version and restart (~1 min).'));
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
     * Manually re-query a single node's daemon now.
     */
    public function refresh(Node $node): RedirectResponse
    {
        return redirect()->route('admin.nodes.index')->with($this->detect($node));
    }

    /**
     * Contact the node's daemon to auto-detect memory/disk and online status.
     * Returns the flash payload describing the outcome.
     *
     * @return array<string, string>
     */
    private function detect(Node $node): array
    {
        if (! $this->wings->refresh($node)) {
            return ['error' => "Saved, but the daemon at {$node->fqdn}:{$node->daemon_port} could not be reached. Check the FQDN, port and token."];
        }

        return ['status' => sprintf(
            'Node online. Detected %s RAM and %s disk.',
            Format::size($node->memory_mb),
            Format::size($node->disk_mb),
        )];
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
            'daemon_tls' => ['sometimes', 'boolean'],
            'description' => ['nullable', 'string'],
        ]);
    }
}
