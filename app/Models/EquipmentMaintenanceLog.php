<?php

namespace App\Models;

use App\LogType;
use Illuminate\Database\Eloquent\Model;

class EquipmentMaintenanceLog extends Model
{
    protected $guarded = [];

    protected $casts = [
        'log_type' => LogType::class,
    ];
}
