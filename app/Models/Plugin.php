<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * Stored state for a discovered plugin (enabled/disabled). Metadata comes from
 * the plugin's plugin.json, resolved by the PluginManager.
 */
#[Fillable(['id', 'enabled'])]
class Plugin extends Model
{
    protected $keyType = 'string';

    public $incrementing = false;

    protected function casts(): array
    {
        return ['enabled' => 'boolean'];
    }
}
