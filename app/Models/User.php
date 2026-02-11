<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
<<<<<<< HEAD
=======

use Illuminate\Database\Eloquent\Concerns\HasUuids;
>>>>>>> 3c15039e2c70c3438132f63d031d6798556f25a4
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
<<<<<<< HEAD
    use HasFactory, Notifiable;
=======
    use HasUuids, HasFactory, Notifiable;
>>>>>>> 3c15039e2c70c3438132f63d031d6798556f25a4

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
<<<<<<< HEAD
=======
        'organization_id',
>>>>>>> 3c15039e2c70c3438132f63d031d6798556f25a4
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
<<<<<<< HEAD
=======

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function bases()
    {
        return $this->belongsToMany(Base::class);
    }

    // Access to Bases
    public function accessibleBaseIds()
    {
        // Boss
        if ($this->role === 'user') {
            return Base::where('organization_id', $this->organization_id)->pluck('id');
        }
        // Operator
        return $this->bases()->pluck('bases.id');
    }

>>>>>>> 3c15039e2c70c3438132f63d031d6798556f25a4
}
