<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $locations = [
            ['name' => 'Jakarta', 'latitude' => -6.2088, 'longitude' => 106.8456],
            ['name' => 'Surabaya', 'latitude' => -7.2575, 'longitude' => 112.7521],
            ['name' => 'Bandung', 'latitude' => -6.9175, 'longitude' => 107.6191],
            ['name' => 'Medan', 'latitude' => 3.5952, 'longitude' => 98.6722],
            ['name' => 'Semarang', 'latitude' => -6.9667, 'longitude' => 110.4167],
            ['name' => 'Makassar', 'latitude' => -5.1477, 'longitude' => 119.4327],
            ['name' => 'Palembang', 'latitude' => -2.9761, 'longitude' => 104.7754],
            ['name' => 'Yogyakarta', 'latitude' => -7.7956, 'longitude' => 110.3695],
            ['name' => 'Denpasar', 'latitude' => -8.6500, 'longitude' => 115.2167],
            ['name' => 'Balikpapan', 'latitude' => -1.2379, 'longitude' => 116.8529],
        ];

        foreach ($locations as $location) {
            Location::create($location);
        }
    }
}
