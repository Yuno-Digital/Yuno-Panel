<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Node;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NodeController extends Controller
{
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
        Node::create($this->validated($request));

        return redirect()->route('admin.nodes.index')->with('status', 'Node created.');
    }

    public function edit(Node $node): View
    {
        return view('admin.nodes.edit', compact('node'));
    }

    public function update(Request $request, Node $node): RedirectResponse
    {
        $node->update($this->validated($request));

        return redirect()->route('admin.nodes.index')->with('status', 'Node updated.');
    }

    public function destroy(Node $node): RedirectResponse
    {
        $node->delete();

        return redirect()->route('admin.nodes.index')->with('status', 'Node deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'fqdn' => ['required', 'string', 'max:255'],
            'daemon_port' => ['required', 'integer', 'min:1', 'max:65535'],
            'memory_mb' => ['required', 'integer', 'min:0'],
            'disk_mb' => ['required', 'integer', 'min:0'],
            'description' => ['nullable', 'string'],
        ]);

        $data['is_online'] = $request->boolean('is_online');

        return $data;
    }
}
