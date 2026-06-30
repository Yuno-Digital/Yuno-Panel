<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['node_id', 'ip', 'port', 'server_id'])]
class Allocation extends Model
{
    protected function casts(): array
    {
        return ['port' => 'integer'];
    }

    /**
     * Human-readable IP:port.
     */
    public function address(): string
    {
        return "{$this->ip}:{$this->port}";
    }

    /**
     * Only allocations not yet bound to a server.
     *
     * @param  Builder<Allocation>  $query
     */
    public function scopeFree(Builder $query): void
    {
        $query->whereNull('server_id');
    }

    /**
     * The node this allocation belongs to.
     *
     * @return BelongsTo<Node, $this>
     */
    public function node(): BelongsTo
    {
        return $this->belongsTo(Node::class);
    }

    /**
     * The server bound to this allocation, if any.
     *
     * @return BelongsTo<Server, $this>
     */
    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }
}
