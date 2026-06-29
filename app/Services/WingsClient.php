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
}
