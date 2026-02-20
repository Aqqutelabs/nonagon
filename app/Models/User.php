<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function bases()
    {
        return $this->belongsToMany(Base::class);
    }

    public function locationAssignments()
    {
        return $this->hasMany(LocationUser::class);
    }

    public function baseIds()
    {
        return $this->locationAssignments()
            ->where('location_type', 'base')
            ->pluck('location_id')
            ->toArray();
    }

    public function siteIds()
    {
        return $this->locationAssignments()
            ->where('location_type', 'site')
            ->pluck('location_id')
            ->toArray();
    }

    public function plantIds()
    {
        return $this->locationAssignments()
            ->where('location_type', 'plant')
            ->pluck('location_id')
            ->toArray();
    }

    public function unitIds()
    {
        return $this->locationAssignments()
            ->where('location_type', 'unit')
            ->pluck('location_id')
            ->toArray();
    }

    public function canAccessEquipment($equipment)
    {
        if ($this->role === 'owner') {
            return true;
        }

        return in_array($equipment->base_id, $this->baseIds())
            || in_array($equipment->site_id, $this->siteIds())
            || in_array($equipment->plant_id, $this->plantIds())
            || in_array($equipment->unit_id, $this->unitIds());
    }

    // public function accessibleUnitIds()
    // {
    //     if ($this->role === 'owner') {
    //         return Unit::where('organization_id', $this->organization_id)
    //             ->pluck('id');
    //     }

    //     $assignments = LocationUser::where('user_id', $this->id)->get();

    //     $unitIds = collect();

    //     foreach ($assignments as $a) {

    //         if ($a->location_type === 'unit') {
    //             $unitIds->push($a->location_id);
    //         }

    //         if ($a->location_type === 'plant') {
    //             $unitIds = $unitIds->merge(
    //                 Unit::where('plant_id', $a->location_id)->pluck('id')
    //             );
    //         }

    //         if ($a->location_type === 'site') {
    //             $plantIds = Plant::where('site_id', $a->location_id)->pluck('id');

    //             $unitIds = $unitIds->merge(
    //                 Unit::whereIn('plant_id', $plantIds)->pluck('id')
    //             );
    //         }

    //         if ($a->location_type === 'base') {
    //             $siteIds = Site::where('base_id', $a->location_id)->pluck('id');

    //             $plantIds = Plant::whereIn('site_id', $siteIds)->pluck('id');

    //             $unitIds = $unitIds->merge(
    //                 Unit::whereIn('plant_id', $plantIds)->pluck('id')
    //             );
    //         }

    //         if ($a->location_type === 'organization') {
    //             return Unit::where('organization_id', $this->organization_id)
    //                 ->pluck('id');
    //         }
    //     }

    //     return $unitIds->unique()->values();
    // }


    // Access to Bases
    // public function accessibleBaseIds()
    // {
    //     // Boss
    //     if ($this->role === 'user') {
    //         return Base::where('organization_id', $this->organization_id)->pluck('id');
    //     }
    //     // Operator
    //     return $this->bases()->pluck('bases.id');
    // }

}
