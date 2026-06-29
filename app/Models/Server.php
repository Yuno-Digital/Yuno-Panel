<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

#[Fillable(['name', 'node_id', 'owner_id', 'status', 'memory_mb', 'disk_mb', 'port'])]
class Server extends Model
{
    protected function casts(): array
    {
        return [
            'memory_mb' => 'integer',
            'disk_mb' => 'integer',
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
}
