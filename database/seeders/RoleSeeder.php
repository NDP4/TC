<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create roles
        $adminRoleId = DB::table('roles')->insertGetId([
            'name' => 'admin',
            'description' => 'Administrator',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $verifikatorRoleId = DB::table('roles')->insertGetId([
            'name' => 'verifikator',
            'description' => 'Verifikator',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create admin user
        $adminUser = User::create([
            'name' => 'Admin User',
            'email' => 'admin@tc.local',
            'password' => Hash::make('admin123'),
            'phone_number' => '+628111111111',
            'skill_level_id' => 5, // Master
            'reputation_score' => 5.0,
            'is_active' => true,
        ]);
        // Assign admin role
        DB::table('role_user')->insert([
            'user_id' => $adminUser->id,
            'role_id' => $adminRoleId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create verifikator user
        $verUser = User::create([
            'name' => 'Verifikator User',
            'email' => 'verifikator@tc.local',
            'password' => Hash::make('verif123'),
            'phone_number' => '+628222222222',
            'skill_level_id' => 4, // Expert
            'reputation_score' => 4.9,
            'is_active' => true,
        ]);
        // Assign verifikator role
        DB::table('role_user')->insert([
            'user_id' => $verUser->id,
            'role_id' => $verifikatorRoleId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
