<?php

namespace App\Services;

use App\Models\Node;
use App\Models\Server;
use Illuminate\Http\Client\PendingRequest;
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

    /**
     * Build an authenticated HTTP client for a server's node daemon.
     */
    private function daemon(Server $server): PendingRequest
    {
        return Http::withToken((string) $server->node?->daemon_token)
            ->timeout(10)
            ->acceptJson();
    }

    /**
     * Full daemon URL for a server endpoint.
     */
    private function url(Server $server, string $path = ''): string
    {
        return $server->node?->daemonUrl().'/api/servers/'.$server->uuid.$path;
    }

    /**
     * Create (install) the server's container from its egg image, resolved
     * startup command, variables and allocation. Returns true on success.
     */
    public function createContainer(Server $server): bool
    {
        $env = $this->environment($server);

        $command = (string) $server->startup;
        foreach ($env as $key => $value) {
            $command = str_replace('{{'.$key.'}}', (string) $value, $command);
        }

        try {
            return $this->daemon($server)->post($this->url($server), [
                'image' => $server->docker_image,
                'command' => $command,
                'memory_mb' => $server->memory_mb,
                'swap_mb' => $server->swap_mb,
                'cpu' => $server->cpu,
                'ports' => array_values(array_filter([$server->allocation?->port])),
                'env' => $env,
                'install_script' => (string) $server->egg?->script_install,
                'install_container' => (string) $server->egg?->script_container,
                'install_entry' => (string) $server->egg?->script_entry,
            ])->successful();
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * The streamed install log for a server.
     */
    public function installLog(Server $server): string
    {
        return (string) ($this->get($server, '/install-log')['log'] ?? '');
    }

    /**
     * Send a power action (start|stop|restart).
     */
    public function power(Server $server, string $action): bool
    {
        try {
            return $this->daemon($server)->post($this->url($server, '/power'), ['action' => $action])->successful();
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Send a console command to the running server.
     */
    public function command(Server $server, string $command): bool
    {
        try {
            return $this->daemon($server)->post($this->url($server, '/command'), ['command' => $command])->successful();
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Resource stats for the server, or null if unreachable.
     *
     * @return array<string, mixed>|null
     */
    public function stats(Server $server): ?array
    {
        return $this->get($server, '/stats');
    }

    /**
     * Tail of the server console output.
     */
    public function logs(Server $server, int $lines = 200): string
    {
        return (string) ($this->get($server, '/logs', ['lines' => $lines])['logs'] ?? '');
    }

    /**
     * List a directory in the server's files.
     *
     * @return array<int, array<string, mixed>>
     */
    public function files(Server $server, string $path = '/'): array
    {
        return $this->get($server, '/files', ['path' => $path])['entries'] ?? [];
    }

    /**
     * Read a file's contents.
     */
    public function fileContents(Server $server, string $path): ?string
    {
        $data = $this->get($server, '/files/contents', ['path' => $path]);

        return $data === null ? null : (string) ($data['contents'] ?? '');
    }

    /**
     * Write a file.
     */
    public function writeFile(Server $server, string $path, string $contents): bool
    {
        try {
            return $this->daemon($server)->post($this->url($server, '/files/write'), compact('path', 'contents'))->successful();
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * GET helper returning the decoded body or null on failure.
     *
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>|null
     */
    private function get(Server $server, string $path, array $query = []): ?array
    {
        try {
            $response = $this->daemon($server)->get($this->url($server, $path), $query);
        } catch (Throwable) {
            return null;
        }

        return $response->successful() ? $response->json() : null;
    }

    /**
     * Build the environment passed to the container: the egg variables plus the
     * standard SERVER_* values used in startup commands.
     *
     * @return array<string, string>
     */
    private function environment(Server $server): array
    {
        $env = [];
        foreach ($server->variables as $variable) {
            $name = $variable->eggVariable?->env_variable;
            if ($name) {
                $env[$name] = (string) $variable->variable_value;
            }
        }

        $env['SERVER_MEMORY'] = (string) $server->memory_mb;
        $env['SERVER_PORT'] = (string) ($server->allocation?->port ?? '');
        $env['SERVER_IP'] = (string) ($server->allocation?->ip ?? '0.0.0.0');

        return $env;
    }
}
