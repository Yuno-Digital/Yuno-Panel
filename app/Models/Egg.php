<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'author', 'description', 'docker_image', 'startup'])]
class Egg extends Model
{
}
