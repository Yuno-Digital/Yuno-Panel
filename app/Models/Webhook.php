<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

#[Fillable(['name', 'url', 'events', 'secret', 'is_active'])]
class Webhook extends Model
{
    /**
     * Events a webhook can subscribe to, as key => human label.
     *
     * @var array<string, string>
     */
    public const EVENTS = [
        'server.created' => 'Server created',
        'server.reinstall' => 'Server (re)installed',
        'server.power' => 'Power action (start/stop/restart)',
    ];

    protected function casts(): array
    {
        return [
            'events' => 'array',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Give new webhooks a signing secret if none was set.
     */
    protected static function booted(): void
    {
        static::creating(function (Webhook $webhook) {
            $webhook->secret ??= 'whsec_'.Str::random(40);
        });
    }

    /**
     * Whether this webhook is active and subscribed to the given event.
     */
    public function subscribesTo(string $event): bool
    {
        return $this->is_active && in_array($event, $this->events ?? [], true);
    }
}
