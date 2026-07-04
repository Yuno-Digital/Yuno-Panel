<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'permissions', 'is_default'])]
class Role extends Model
{
    /**
     * The permissions the panel understands, as key => human label.
     * `administrator` grants full access.
     *
     * @var array<string, string>
     */
    public const PERMISSIONS = [
        'administrator' => 'Full administrator access',
        'servers.manage' => 'Create and manage all servers',
        'nodes.manage' => 'Manage nodes and allocations',
        'eggs.manage' => 'Manage eggs',
        'users.manage' => 'Manage users and roles',
        'settings.manage' => 'Change panel settings',
    ];

    protected function casts(): array
    {
        return [
            'permissions' => 'array',
            'is_default' => 'boolean',
        ];
    }

    /**
     * Users assigned to this role.
     *
     * @return HasMany<User, $this>
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Whether this role grants the given permission (administrators grant all).
     */
    public function hasPermission(string $permission): bool
    {
        $permissions = $this->permissions ?? [];

        return in_array('administrator', $permissions, true)
            || in_array($permission, $permissions, true);
    }
}
