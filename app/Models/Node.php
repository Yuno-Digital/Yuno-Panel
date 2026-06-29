<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'fqdn', 'daemon_port', 'daemon_token', 'is_online', 'memory_mb', 'disk_mb', 'description'])]
#[Hidden(['daemon_token'])]
class Node extends Model
{
    protected function casts(): array
    {
        return [
            'is_online' => 'boolean',
            'daemon_port' => 'integer',
            'memory_mb' => 'integer',
            'disk_mb' => 'integer',
        ];
    }

    /**
     * Base URL of this node's Wings daemon, e.g. http://host:8080.
     */
    public function daemonUrl(): string
    {
        return sprintf('http://%s:%d', $this->fqdn, $this->daemon_port);
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
}
