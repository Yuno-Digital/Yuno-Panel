<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Node;
use App\Services\WingsClient;
use App\Support\Format;
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
            'daemon_token' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);
    }
}
