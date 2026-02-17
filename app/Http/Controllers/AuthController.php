<?php

namespace App\Http\Controllers;

use App\Models\Base;
use App\Models\Invitation;
use App\Models\LocationUser;
use App\Models\Organization;
use App\Models\Plant;
use App\Models\Site;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'organization_name' => 'required|string|max:255',
            'organization_email' => 'nullable|email|unique:organizations,email',
            'organization_phone' => 'nullable|string|max:20',
            'organization_address' => 'nullable|string|max:255',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $data = DB::transaction(function () use ($request) {

            // Create Organization
            $organization = Organization::create([
                'id' => Str::uuid(),
                'name' => $request->organization_name,
                'email' => $request->organization_email,
                'phone_no' => $request->organization_phone,
                'address' => $request->organization_address,
            ]);

            // Create Owner User
            $user = User::create([
                'id' => Str::uuid(),
                'organization_id' => $organization->id,
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'owner',
            ]);

            // Default Base
            $base = Base::create([
                'id' => Str::uuid(),
                'organization_id' => $organization->id,
                'name' => 'Default',
                'is_default' => true,
            ]);

            // Default Site
            $site = Site::create([
                'id' => Str::uuid(),
                'organization_id' => $organization->id,
                'base_id' => $base->id,
                'name' => 'Default',
                'is_default' => true,
            ]);

            // Default Plant
            $plant = Plant::create([
                'id' => Str::uuid(),
                'organization_id' => $organization->id,
                'base_id' => $base->id,
                'site_id' => $site->id,
                'name' => 'Default',
                'is_default' => true,
            ]);

            // Default Unit
            $unit = Unit::create([
                'id' => Str::uuid(),
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
                'location_type' => 'organization',
                'location_id' => $organization->id,
            ]);

            return [
                'organization' => $organization,
                'user' => $user
            ];
        });

        // Login the user immediately
        auth()->login($data['user']);

        return response()->json([
            'message' => 'Organization and owner account created successfully',
            'organization' => $data['organization'],
            'user' => $data['user'],
            'token' => $data['user']->createToken('auth_token')->plainTextToken,
        ]);
    }

    // Login
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }

        if (! $user->is_active) {
            return response()->json([
                'message' => 'Account is inactive'
            ], 403);
        }

        // email verification
        // if (! $user->email_verified_at) {
        //     return response()->json([
        //         'message' => 'Email not verified'
        //     ], 403);
        // }

        // Login
        auth()->login($user);

        return response()->json([
            'message' => 'Login successful',
            'user' => $user,
            'token' => $user->createToken('auth_token')->plainTextToken,
        ]);
    }

    // Send Invite
    public function sendInvite(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'location_type' => 'required',
            'location_id' => 'required|uuid',
        ]);

        $user = auth()->user();

        $table = match ($request->location_type) {
            'base' => 'bases',
            'site' => 'sites',
            'plant' => 'plants',
            'unit' => 'units',
        };

        $exists = DB::table($table)
            ->where('id', $request->location_id)
            ->where('organization_id', $user->organization_id)
            ->exists();

        if (!$exists) {
            abort(404);
        }

        $token = Str::random(64);

        $invite = Invitation::create([
            'organization_id' => $user->organization_id,
            'email' => $request->email,
            'role' => 'operator',
            'location_type' => $request->location_type,
            'location_id' => $request->location_id,
            'token' => hash('sha256', $token),
            'expires_at' => now()->addDays(7),
            'created_by' => $user->id,
        ]);

        // Mail::to($request->email)->send(
        //     new InviteMail($token)
        // );

        return response()->json(['message' => 'Invite sent']);
    }

    public function acceptInvite($token)
    {
        $invite = Invitation::where('token', hash('sha256', $token))->firstOrFail();

        if ($invite->accepted_at) {
            return response()->json(['message' => 'Already accepted'], 400);
        }

        if ($invite->expires_at < now()) {
            return response()->json(['message' => 'Invite expired'], 400);
        }

        return response()->json([
            'email' => $invite->email
        ]);
    }

    public function completeInvite(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'name' => 'required|string|max:255',
            'password' => 'required|min:6'
        ]);

        $invite = Invitation::where('token', hash('sha256', $request->token))->firstOrFail();

        DB::transaction(function () use ($invite, $request) {

            $user = User::create([
                'name' => $request->name,
                'email' => $invite->email,
                'password' => bcrypt($request->password),
                'organization_id' => $invite->organization_id,
            ]);

            LocationUser::create([
                'user_id' => $user->id,
                'organization_id' => $invite->organization_id,
                'location_type' => $invite->location_type, // site/unit/etc
                'location_id' => $invite->location_id,
            ]);

            $invite->update([
                'accepted_at' => now()
            ]);
        });

        return response()->json(['message' => 'Account created']);
    }
}
