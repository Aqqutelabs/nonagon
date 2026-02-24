<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Equipment extends Model
{
    use HasUuids, HasFactory;
    protected $guarded = [];

    public function category()
    {
        return $this->belongsTo(EquipmentCategory::class, 'equipment_category_id');
    }

    public function status()
    {
        return $this->belongsTo(EquipmentStatus::class, 'equipment_status_id');
    }

    // public function operator()
    // {
    //     return $this->belongsTo(User::class, 'operator_id');
    // }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function plant()
    {
        return $this->belongsTo(Plant::class);
    }

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function base()
    {
        return $this->belongsTo(Base::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function depreciationProfile()
    {
        return $this->hasOne(EquipmentDepreciationProfile::class);
    }

    public function snapshot()
    {
        return $this->hasOne(EquipmentValueSnapshot::class);
    }

    public function value()
    {
        return $this->hasOne(EquipmentValueCurrent::class);
    }

    public function maintenanceWorkOrder()
    {
        return $this->hasOne(MaintenanceWorkOrder::class);
    }

    public function breakdownIncident()
    {
        return $this->hasMany(BreakdownIncident::class);
    }

    public function maintenanceSchedule()
    {
        return $this->hasOne(MaintenanceSchedule::class);
    }

    public function maintenanceLog()
    {
        return $this->hasMany(EquipmentMaintenanceLog::class);
    }
}
