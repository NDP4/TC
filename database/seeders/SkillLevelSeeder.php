<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SkillLevel;

class SkillLevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $skillLevels = [
            ['level_name' => 'Beginner', 'multiplier' => 0.75],
            ['level_name' => 'Intermediate', 'multiplier' => 1.00],
            ['level_name' => 'Advanced', 'multiplier' => 1.25],
            ['level_name' => 'Expert', 'multiplier' => 1.50],
            ['level_name' => 'Master', 'multiplier' => 2.00],
        ];

        foreach ($skillLevels as $skillLevel) {
            SkillLevel::create($skillLevel);
        }
    }
}
