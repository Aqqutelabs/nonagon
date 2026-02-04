<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Base extends Model
{
    use HasUuids;

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
}
