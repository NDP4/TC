<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\SkillLevel;
use Illuminate\Database\Seeder;

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
