<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * A webhook scoped to a single server: the owner can POST that server's events
 * to their own endpoints, independent of the global (admin) webhooks.
 */
#[Fillable(['server_id', 'url', 'events', 'secret', 'is_active'])]
class ServerWebhook extends Model
{
    /**
     * Events a server webhook can subscribe to, as key => human label.
     *
     * @var array<string, string>
     */
    public const EVENTS = [
        'server.power' => 'Power action (start/stop/restart)',
        'server.reinstall' => 'Server (re)installed',
    ];

    protected function casts(): array
    {
        return [
            'events' => 'array',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (ServerWebhook $webhook) {
            $webhook->secret ??= 'whsec_'.Str::random(40);
        });
    }

    /**
     * @return BelongsTo<Server, $this>
     */
    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }

    /**
     * Whether this webhook is active and subscribed to the given event.
     */
    public function subscribesTo(string $event): bool
    {
        return $this->is_active && in_array($event, $this->events ?? [], true);
    }
}
