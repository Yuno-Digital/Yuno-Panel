<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

#[Fillable([
    'name', 'icon', 'node_id', 'allocation_id', 'owner_id', 'egg_id', 'docker_image', 'startup',
    'status', 'memory_mb', 'disk_mb', 'cpu', 'swap_mb', 'port',
])]
class Server extends Model
{
    /**
     * Permissions a subuser can be granted on a server, as key => human label.
     *
     * @var array<string, string>
     */
    public const SUBUSER_PERMISSIONS = [
        'console' => 'View console & send commands',
        'power' => 'Start / stop / restart',
        'files' => 'Manage files',
        'startup' => 'Edit startup & variables',
        'reinstall' => 'Reinstall the server',
    ];

    /**
     * The icon to show for this server: its own, or the egg's as a fallback.
     */
    public function displayIcon(): ?string
    {
        return $this->icon ?: $this->egg?->icon;
    }

    /**
     * Users granted access to this server (with their permissions on the pivot).
     *
     * @return BelongsToMany<User, $this>
     */
    public function subusers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'server_subusers')
            ->using(ServerSubuser::class)
            ->withPivot('permissions')
            ->withTimestamps();
    }

    /**
     * The subuser permissions for a user, or null if they are not a subuser.
     *
     * @return array<int, string>|null
     */
    public function subuserPermissions(User $user): ?array
    {
        $row = DB::table('server_subusers')
            ->where('server_id', $this->id)
            ->where('user_id', $user->id)
            ->value('permissions');

        return $row === null ? null : (json_decode((string) $row, true) ?: []);
    }

    /**
     * Whether the user may access this server at all (owner, admin or subuser).
     */
    public function accessibleBy(User $user): bool
    {
        return $user->isAdmin()
            || $this->owner_id === $user->id
            || $this->subuserPermissions($user) !== null;
    }

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
