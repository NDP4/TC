<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name'             => 'John Doe',
                'email'            => 'john@example.com',
                'password'         => Hash::make('password123'),
                'phone_number'     => '+628123456789',
                'skill_level_id'   => 3, // Advanced
                'reputation_score' => 4.8,
                'is_active'        => true,
            ],
            [
                'name'             => 'Jane Smith',
                'email'            => 'jane@example.com',
                'password'         => Hash::make('password123'),
                'phone_number'     => '+628987654321',
                'skill_level_id'   => 2, // Intermediate
                'reputation_score' => 4.2,
                'is_active'        => true,
            ],
            [
                'name'             => 'Bob Wilson',
                'email'            => 'bob@example.com',
                'password'         => Hash::make('password123'),
                'phone_number'     => '+628555666777',
                'skill_level_id'   => 1, // Beginner
                'reputation_score' => 3.5,
                'is_active'        => true,
            ],
            [
                'name'             => 'Alice Johnson',
                'email'            => 'alice@example.com',
                'password'         => Hash::make('password123'),
                'phone_number'     => '+628999888777',
                'skill_level_id'   => 4, // Expert
                'reputation_score' => 4.9,
                'is_active'        => false,
            ],
        ];

        foreach ($users as $userData) {
            User::create($userData);
        }

        $this->command->info('Sample users created successfully!');
    }
}
