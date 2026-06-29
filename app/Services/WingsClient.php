<?php

namespace App\Services;

use App\Models\Node;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Talks to a node's Wings daemon over its authenticated HTTP API.
 */
class WingsClient
{
    /**
     * Fetch the daemon's /api/system payload (version, docker status, detected
     * memory_mb and disk_mb). Returns null if the node cannot be reached or the
     * token is rejected.
     *
     * @return array<string, mixed>|null
     */
    public function system(Node $node): ?array
    {
        try {
            $response = Http::withToken((string) $node->daemon_token)
                ->timeout(5)
                ->acceptJson()
                ->get($node->daemonUrl().'/api/system');
        } catch (Throwable) {
            return null;
        }

        if (! $response->successful()) {
            return null;
        }

        return $response->json();
    }

    /**
     * Query the node's daemon and persist the result: on success, mark it
     * online and store the detected memory/disk; otherwise mark it offline.
     * Returns true if the node was reachable.
     */
    public function refresh(Node $node): bool
    {
        $system = $this->system($node);

        if ($system === null) {
            $node->forceFill(['is_online' => false])->save();

            return false;
        }

        $node->forceFill([
            'is_online' => true,
            'memory_mb' => (int) ($system['memory_mb'] ?? 0),
            'disk_mb' => (int) ($system['disk_mb'] ?? 0),
        ])->save();

        return true;
    }
}
