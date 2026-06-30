<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['server_id', 'egg_variable_id', 'variable_value'])]
class ServerVariable extends Model
{
    /**
     * The server this value belongs to.
     *
     * @return BelongsTo<Server, $this>
     */
    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }

    /**
     * The egg variable this value is for.
     *
     * @return BelongsTo<EggVariable, $this>
     */
    public function eggVariable(): BelongsTo
    {
        return $this->belongsTo(EggVariable::class);
    }
}
