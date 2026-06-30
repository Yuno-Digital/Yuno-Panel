<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Allocation;
use App\Models\Node;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AllocationController extends Controller
{
    /**
     * Create one or more allocations on a node. Ports accept single values,
     * ranges (25565-25570) and comma-separated lists.
     */
    public function store(Request $request, Node $node): RedirectResponse
    {
        $data = $request->validate([
            'ip' => ['required', 'string', 'max:255'],
            'ports' => ['required', 'string', 'max:255'],
        ]);

        $ports = $this->parsePorts($data['ports']);

        if ($ports === []) {
            return back()->with('error', __('No valid ports given. Use e.g. 25565, 25565-25570.'));
        }

        $created = 0;
        foreach ($ports as $port) {
            $allocation = Allocation::firstOrCreate([
                'node_id' => $node->id,
                'ip' => $data['ip'],
                'port' => $port,
            ]);
            $created += $allocation->wasRecentlyCreated ? 1 : 0;
        }

        return back()->with('status', trans_choice(':count allocation created.|:count allocations created.', $created, ['count' => $created]));
    }

    /**
     * Delete an allocation (only when it is not bound to a server).
     */
    public function destroy(Node $node, Allocation $allocation): RedirectResponse
    {
        abort_unless($allocation->node_id === $node->id, 404);

        if ($allocation->server_id !== null) {
            return back()->with('error', __('That allocation is in use by a server.'));
        }

        $allocation->delete();

        return back()->with('status', __('Allocation deleted.'));
    }

    /**
     * Expand a port specification into a unique, bounded list of port numbers.
     *
     * @return array<int, int>
     */
    private function parsePorts(string $spec): array
    {
        $ports = [];

        foreach (preg_split('/[\s,]+/', trim($spec)) as $part) {
            if ($part === '') {
                continue;
            }

            if (preg_match('/^(\d+)-(\d+)$/', $part, $m)) {
                $start = (int) $m[1];
                $end = (int) $m[2];
                if ($start <= $end && $end - $start <= 1000) {
                    foreach (range($start, $end) as $port) {
                        $ports[$port] = $port;
                    }
                }
            } elseif (ctype_digit($part)) {
                $port = (int) $part;
                $ports[$port] = $port;
            }
        }

        return array_values(array_filter($ports, fn ($p) => $p >= 1 && $p <= 65535));
    }
}
