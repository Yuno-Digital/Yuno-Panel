<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable([
    'name', 'node_id', 'allocation_id', 'owner_id', 'egg_id', 'docker_image', 'startup',
    'status', 'memory_mb', 'disk_mb', 'cpu', 'swap_mb', 'port',
])]
class Server extends Model
{
    protected function casts(): array
    {
        return [
            'memory_mb' => 'integer',
            'disk_mb' => 'integer',
            'cpu' => 'integer',
            'swap_mb' => 'integer',
            'port' => 'integer',
        ];
    }

    /**
     * Auto-assign a UUID on creation.
     */
    protected static function booted(): void
    {
        static::creating(function (Server $server) {
            $server->uuid ??= (string) Str::uuid();
        });
    }

    /**
     * The node this server runs on.
     *
     * @return BelongsTo<Node, $this>
     */
    public function node(): BelongsTo
    {
        return $this->belongsTo(Node::class);
    }

    /**
     * The user who owns this server.
     *
     * @return BelongsTo<User, $this>
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * The egg this server was created from.
     *
     * @return BelongsTo<Egg, $this>
     */
    public function egg(): BelongsTo
    {
        return $this->belongsTo(Egg::class);
    }

    /**
     * The server's primary IP:port allocation.
     *
     * @return BelongsTo<Allocation, $this>
     */
    public function allocation(): BelongsTo
    {
        return $this->belongsTo(Allocation::class);
    }

    /**
     * The server's filled-in egg variable values.
     *
     * @return HasMany<ServerVariable, $this>
     */
    public function variables(): HasMany
    {
        return $this->hasMany(ServerVariable::class);
    }
}
