<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ServerSubuser extends Pivot
{
    protected $table = 'server_subusers';

    public $incrementing = true;

    protected function casts(): array
    {
        return ['permissions' => 'array'];
    }
}
