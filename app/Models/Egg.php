<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable([
    'name', 'author', 'description', 'tags', 'features',
    'docker_image', 'docker_images', 'file_denylist', 'update_url',
    'startup', 'startup_commands', 'config_from', 'config_startup', 'config_stop', 'config_files', 'config_logs',
    'copy_script_from', 'script_container', 'script_entry', 'script_is_privileged', 'script_install',
])]
class Egg extends Model
{
    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'features' => 'array',
            'docker_images' => 'array',
            'startup_commands' => 'array',
            'file_denylist' => 'array',
            'script_is_privileged' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Egg $egg) {
            $egg->uuid ??= (string) Str::uuid();
        });
    }

    /**
     * Configurable variables exposed by this egg.
     *
     * @return HasMany<EggVariable, $this>
     */
    public function variables(): HasMany
    {
        return $this->hasMany(EggVariable::class);
    }

    /**
     * The egg this one copies process configuration from.
     *
     * @return BelongsTo<Egg, $this>
     */
    public function configFrom(): BelongsTo
    {
        return $this->belongsTo(Egg::class, 'config_from');
    }

    /**
     * The egg this one copies its install script from.
     *
     * @return BelongsTo<Egg, $this>
     */
    public function copyScriptFrom(): BelongsTo
    {
        return $this->belongsTo(Egg::class, 'copy_script_from');
    }
}
