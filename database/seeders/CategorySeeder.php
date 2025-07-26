<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Technology', 'description' => 'Technology-related services including programming, web development, and IT support'],
            ['name' => 'Education', 'description' => 'Educational services including tutoring, training, and consulting'],
            ['name' => 'Design', 'description' => 'Creative design services including graphic design, UI/UX, and branding'],
            ['name' => 'Writing', 'description' => 'Content creation services including copywriting, blogging, and technical writing'],
            ['name' => 'Marketing', 'description' => 'Marketing and advertising services including social media management and SEO'],
            ['name' => 'Business', 'description' => 'Business consulting and administrative services'],
            ['name' => 'Health & Wellness', 'description' => 'Health and wellness services including fitness training and nutrition counseling'],
            ['name' => 'Lifestyle', 'description' => 'Lifestyle and personal services'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
