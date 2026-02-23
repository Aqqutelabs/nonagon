<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasUuids;

    protected $guarded = [];

    public function plan()
    {
        return $this->belongsTo(SubscriptionTemplate::class);
    }
}
