<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Airline;

class AirlineSeeder extends Seeder
{
    public function run()
    {
        $airlines = [
            ['name' => 'GARUDA INDONESIA AIRLINE', 'code' => 'GA', "price" => 1000000],
            ['name' => 'LION AIR', 'code' => 'JT', "price" => 1000000],
            ['name' => 'BATIK AIR', 'code' => 'ID', "price" => 1000000],
            ['name' => 'RIMBUN AIR', 'code' => 'RI', "price" => 1000000],
            ['name' => 'WINGS AIR', 'code' => 'IW', "price" => 1000000],
            ['name' => 'TRIGANA AIR', 'code' => 'IL', "price" => 1000000],
            ['name' => 'HERCULES', 'code' => 'C-130', "price" => 1000000],
            ['name' => 'CARTER', 'code' => 'CHARTER', "price" => 1000000],
        ];
        foreach ($airlines as $airline) {
            Airline::updateOrCreate(['code' => $airline['code']], $airline);
        }
    }
}
