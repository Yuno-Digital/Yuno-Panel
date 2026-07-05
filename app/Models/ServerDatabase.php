<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A database provisioned for a server on a DatabaseHost (real MySQL database +
 * user with the stored credentials).
 */
#[Fillable(['server_id', 'database_host_id', 'database', 'username', 'password', 'remote'])]
class ServerDatabase extends Model
{
    protected function casts(): array
    {
        return ['password' => 'encrypted'];
    }

    /**
     * @return BelongsTo<Server, $this>
     */
    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }

    /**
     * @return BelongsTo<DatabaseHost, $this>
     */
    public function host(): BelongsTo
    {
        return $this->belongsTo(DatabaseHost::class, 'database_host_id');
    }
}
