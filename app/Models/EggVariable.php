<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'egg_id', 'name', 'description', 'env_variable',
    'default_value', 'user_viewable', 'user_editable', 'rules',
])]
class EggVariable extends Model
{
    protected function casts(): array
    {
        return [
            'user_viewable' => 'boolean',
            'user_editable' => 'boolean',
        ];
    }

    /**
     * The egg this variable belongs to.
     *
     * @return BelongsTo<Egg, $this>
     */
    public function egg(): BelongsTo
    {
        return $this->belongsTo(Egg::class);
    }
}
