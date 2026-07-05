<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Throwable;

/**
 * An audit-log entry for a server: who did what, when, and from where.
 */
#[Fillable(['server_id', 'user_id', 'event', 'properties', 'ip_address'])]
class ServerActivity extends Model
{
    protected function casts(): array
    {
        return ['properties' => 'array'];
    }

    /**
     * @return BelongsTo<Server, $this>
     */
    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Record an activity. Never throws — auditing must not break the action it
     * describes. The actor defaults to the logged-in user (null for system /
     * scheduled events).
     *
     * @param  array<string, mixed>  $properties
     */
    public static function record(Server $server, string $event, array $properties = [], ?User $actor = null): void
    {
        try {
            $server->activities()->create([
                'user_id' => ($actor ?? auth()->user())?->id,
                'event' => $event,
                'properties' => $properties !== [] ? $properties : null,
                'ip_address' => rescue(fn () => request()?->ip(), null, false),
            ]);
        } catch (Throwable) {
            // ignore
        }
    }

    /**
     * A human-readable description of this entry.
     */
    public function describe(): string
    {
        $p = $this->properties ?? [];

        return match ($this->event) {
            'server:power' => __('Power action: :a', ['a' => $p['action'] ?? '?']),
            'server:command' => __('Sent command: :c', ['c' => $p['command'] ?? '']),
            'server:settings' => __('Updated startup & variables'),
            'server:reinstall' => __('Reinstalled the server'),
            'file:write' => __('Edited file :p', ['p' => $p['path'] ?? '']),
            'file:delete' => __('Deleted :n file(s)', ['n' => $p['count'] ?? 0]),
            'subuser:updated' => __('Saved subuser :u', ['u' => $p['user'] ?? '']),
            'subuser:removed' => __('Removed subuser :u', ['u' => $p['user'] ?? '']),
            'schedule:created' => __('Created schedule ":n"', ['n' => $p['name'] ?? '']),
            'schedule:toggled' => ($p['active'] ?? false)
                ? __('Enabled schedule ":n"', ['n' => $p['name'] ?? ''])
                : __('Paused schedule ":n"', ['n' => $p['name'] ?? '']),
            'schedule:deleted' => __('Deleted schedule ":n"', ['n' => $p['name'] ?? '']),
            'schedule:ran' => __('Ran schedule ":n"', ['n' => $p['name'] ?? '']),
            'database:created' => __('Created database :n', ['n' => $p['name'] ?? '']),
            'database:deleted' => __('Deleted database :n', ['n' => $p['name'] ?? '']),
            'backup:created' => __('Created backup ":n"', ['n' => $p['name'] ?? '']),
            'backup:restored' => __('Restored backup ":n"', ['n' => $p['name'] ?? '']),
            'backup:deleted' => __('Deleted backup ":n"', ['n' => $p['name'] ?? '']),
            default => $this->event,
        };
    }
}
