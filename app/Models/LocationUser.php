<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class LocationUser extends Model
{
    use HasUuids;

    protected $guarded = [];
}
