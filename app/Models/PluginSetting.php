<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['plugin_id', 'key', 'value'])]
class PluginSetting extends Model
{
    //
}
