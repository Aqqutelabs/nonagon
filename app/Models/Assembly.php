<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Assembly extends Model
{
    protected $guarded = [];

    public function equipment()
    {
        return $this->belongsTo(Equipment::class);
    }
}
