<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaintenanceWorkOrder extends Model
{
    public function task()
    {
        return $this->hasOne(MaintenanceWorkOrderTask::class);
    }

    public function attachment()
    {
        return $this->hasMany(MaintenanceWorkOrderAttachment::class);
    }

    public function breakdonIncident()
    {
        return $this->hasMany(BreakdownIncident::class);
    }
}
