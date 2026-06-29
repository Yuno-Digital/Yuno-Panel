<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'fqdn', 'daemon_port', 'is_online', 'memory_mb', 'disk_mb', 'description'])]
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
     * Servers hosted on this node.
     *
     * @return HasMany<Server, $this>
     */
    public function servers(): HasMany
    {
        return $this->hasMany(Server::class);
    }
}
