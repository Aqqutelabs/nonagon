<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Site extends Model
{
    protected $guarded = [];

    public function base()
    {
        return $this->belongsTo(Base::class);
    }
}
