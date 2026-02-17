<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Organization extends Model
{
    use HasUuids;

    protected $guarded = [];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function subscription()
    {
        return $this->hasOne(Subscription::class)
            ->where('status', 'active');
    }

    // public function hasFeature(string $featureKey): bool
    // {
    //     return DB::table('organization_features')
    //         ->join('feature_registries', 'feature_registries.id', '=', 'organization_features.feature_registry_id')
    //         ->where('organization_features.organization_id', $this->id)
    //         ->where('feature_registries.feature_key', $featureKey)
    //         ->where('organization_features.is_enabled', true)
    //         ->exists();
    // }

    // public function featureLimit(string $featureKey): ?int
    // {
    //     return DB::table('organization_features')
    //         ->join('feature_registries', 'feature_registries.id', '=', 'organization_features.feature_registry_id')
    //         ->where('organization_features.organization_id', $this->id)
    //         ->where('feature_registries.feature_key', $featureKey)
    //         ->value('limit_value');
    // }

    public function hasFeature(string $featureKey): bool
    {
        return DB::table('organization_features')
            ->where('organization_id', $this->id)
            ->where('feature_key', $featureKey)
            ->value('is_enabled') ?? false;
    }

    public function featureLimit(string $featureKey): ?int
    {
        return DB::table('organization_features')
            ->where('organization_id', $this->id)
            ->where('feature_key', $featureKey)
            ->value('limit_value');
    }
}
