<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A backup archive of a server's files, stored on its node.
 */
#[Fillable(['server_id', 'uuid', 'name', 'bytes', 'checksum', 'is_successful', 'completed_at'])]
class Backup extends Model
{
    protected function casts(): array
    {
        return [
            'bytes' => 'integer',
            'is_successful' => 'boolean',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Server, $this>
     */
    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }
}
