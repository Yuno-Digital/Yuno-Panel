<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable(['name', 'fqdn', 'daemon_port', 'daemon_tls', 'daemon_token', 'is_online', 'memory_mb', 'disk_mb', 'description'])]
#[Hidden(['daemon_token'])]
class Node extends Model
{
    protected function casts(): array
    {
        return [
            'is_online' => 'boolean',
            'daemon_tls' => 'boolean',
            'daemon_port' => 'integer',
            'memory_mb' => 'integer',
            'disk_mb' => 'integer',
        ];
    }

    /**
     * Generate a fresh daemon token. The panel owns the token; the daemon
     * fetches it via `wings configure`, so it stays stable across restarts.
     */
    public static function generateToken(): string
    {
        return 'yuno_node_'.Str::random(40);
    }

    /**
     * The daemon configuration handed to `wings configure`.
     *
     * @return array<string, mixed>
     */
    public function daemonConfig(): array
    {
        return [
            'token' => $this->daemon_token,
            'api_host' => '0.0.0.0',
            'api_port' => $this->daemon_port,
            'panel_url' => rtrim((string) config('app.url'), '/'),
            'docker_prefix' => 'yuno',
            'disk_path' => '/',
            'data_path' => '/var/lib/yuno/servers',
        ];
    }

    /**
     * Base URL of this node's Wings daemon, e.g. https://host:8090. Uses HTTPS
     * when the node is marked as TLS-enabled.
     */
    public function daemonUrl(): string
    {
        $scheme = $this->daemon_tls ? 'https' : 'http';

        return sprintf('%s://%s:%d', $scheme, $this->fqdn, $this->daemon_port);
    }

    /**
     * Servers hosted on this node.
     *
     * @return HasMany<Server, $this>
     */
    public function servers(): HasMany
    {
        return $this->hasMany(Server::class);
    }

    /**
     * IP:port allocations available on this node.
     *
     * @return HasMany<Allocation, $this>
     */
    public function allocations(): HasMany
    {
        return $this->hasMany(Allocation::class);
    }
}
