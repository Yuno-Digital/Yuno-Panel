<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A MySQL/MariaDB server the panel provisions per-server databases on, using
 * stored admin credentials.
 */
#[Fillable(['name', 'host', 'port', 'username', 'password', 'linked_host', 'max_databases'])]
#[Hidden(['password'])]
class DatabaseHost extends Model
{
    protected function casts(): array
    {
        return [
            'port' => 'integer',
            'password' => 'encrypted',
            'max_databases' => 'integer',
        ];
    }

    /**
     * Hostname servers should use to reach this database (falls back to host).
     */
    public function connectHost(): string
    {
        return $this->linked_host ?: $this->host;
    }

    /**
     * @return HasMany<ServerDatabase, $this>
     */
    public function databases(): HasMany
    {
        return $this->hasMany(ServerDatabase::class);
    }
}
