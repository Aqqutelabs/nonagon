<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EquipmentMeter extends Model
{
    public function reading()
    {
        return $this->hasOne(EquipmentMeterReading::class);
    }
}
