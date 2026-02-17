<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Equipment extends Model
{
    use HasUuids;
    protected $guarded = [];

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

}
