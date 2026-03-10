<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Base;
use App\Models\LocationUser;
use App\Models\Organization;
use App\Models\Plant;
use App\Models\Site;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/equipments';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'organization_name' => ['nullable', 'string', 'max:255'],
            'organization_email' => ['nullable', 'email', 'unique:organizations,email'],
            'phone_no' => ['nullable', 'string', 'max:20'],
            'organization_address' => ['nullable', 'string', 'max:255'],

            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'position' => ['nullable', 'string'],

            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
        return DB::transaction(function () use ($data) {

            $organizationName = $data['organization_name'] ?? $data['name'];

            // Create Organization
            $organization = Organization::create([
                'name' => $organizationName,
                'email' => $data['organization_email'] ?? null,
                'phone_no' => $data['phone_no'] ?? null,
                'address' => $data['organization_address'] ?? null,
            ]);

            // Create Owner User
            $user = User::create([
                'organization_id' => $organization->id,
                'name' => $data['name'],
                'email' => $data['email'],
                'phone_no' => $data['phone_no'] ?? null,
                'position' => $data['position'] ?? null,
                'password' => Hash::make($data['password']),
                'role' => 'owner',
            ]);

            // Default Base
            $base = Base::create([
                'organization_id' => $organization->id,
                'name' => 'Default',
                'is_default' => true,
            ]);

            // Default Site
            $site = Site::create([
                'organization_id' => $organization->id,
                'base_id' => $base->id,
                'name' => 'Default',
                'is_default' => true,
            ]);

            // Default Plant
            $plant = Plant::create([
                'organization_id' => $organization->id,
                'base_id' => $base->id,
                'site_id' => $site->id,
                'name' => 'Default',
                'is_default' => true,
            ]);

            // Default Unit
            $unit = Unit::create([
                'organization_id' => $organization->id,
                'base_id' => $base->id,
                'site_id' => $site->id,
                'plant_id' => $plant->id,
                'name' => 'Default',
                'is_default' => true,
            ]);

            // Attach creator as organization admin
            LocationUser::create([
                'user_id' => $user->id,
                'organization_id' => $organization->id,
                'location_type' => 'organization',
                'location_id' => $organization->id,
            ]);

            return $user;
        });
    }
}
