<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Airline;

class AirlineSeeder extends Seeder
{
    public function run()
    {
        $airlines = [
            ['name' => 'GARUDA INDONESIA AIRLINE', 'code' => 'GA'],
            ['name' => 'LION AIR', 'code' => 'JT'],
            ['name' => 'BATIK AIR', 'code' => 'ID'],
            ['name' => 'RIMBUN AIR', 'code' => 'RI'],
            ['name' => 'WINGS AIR', 'code' => 'IW'],
            ['name' => 'TRIGANA AIR', 'code' => 'IL'],
            ['name' => 'HERCULES', 'code' => 'C-130'],
            ['name' => 'CARTER', 'code' => 'CHARTER'],
        ];
        foreach ($airlines as $airline) {
            Airline::updateOrCreate(['code' => $airline['code']], $airline);
        }
    }
}
