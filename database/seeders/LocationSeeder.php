<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Location;

class LocationSeeder extends Seeder
{
    public function run()
    {
        $locations = [
            ['name' => 'GORONTALO', 'code' => 'GTO'],
            ['name' => 'JAKARTA', 'code' => 'CGK'],
            ['name' => 'MAKASSAR', 'code' => 'UPG'],
            ['name' => 'MANADO', 'code' => 'MDC'],
            ['name' => 'HALIM PERDANAKUSUMA', 'code' => 'HLM'],
            ['name' => 'CUSTOM', 'code' => 'CUSTOM'],
        ];
        foreach ($locations as $location) {
            Location::updateOrCreate(['code' => $location['code']], $location);
        }
    }
}
