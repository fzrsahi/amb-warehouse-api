<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Airline;

class AirlineSeeder extends Seeder
{
    public function run()
    {
        $airlines = [
            [
                'name' => 'GARUDA INDONESIA AIRLINE',
                'code' => 'GA',
                'cargo_handling_incoming_price' => 5000,
                'cargo_handling_outgoing_price' => 5000,
                'handling_airplane_outgoing_price' => 3000,
                'handling_airplane_incoming_price' => 3000,
                'jppgc_incoming_price' => 10000,
                'jppgc_outgoing_price' => 10000,
            ],
            [
                'name' => 'LION AIR',
                'code' => 'JT',
                'cargo_handling_incoming_price' => 5000,
                'cargo_handling_outgoing_price' => 5000,
                'handling_airplane_outgoing_price' => 3000,
                'handling_airplane_incoming_price' => 3000,
                'jppgc_incoming_price' => 10000,
                'jppgc_outgoing_price' => 10000,
            ],
            [
                'name' => 'BATIK AIR',
                'code' => 'ID',
                'cargo_handling_incoming_price' => 5000,
                'cargo_handling_outgoing_price' => 5000,
                'handling_airplane_outgoing_price' => 3000,
                'handling_airplane_incoming_price' => 3000,
                'jppgc_incoming_price' => 10000,
                'jppgc_outgoing_price' => 10000,
            ],
            [
                'name' => 'RIMBUN AIR',
                'code' => 'RI',
                'cargo_handling_incoming_price' => 5000,
                'cargo_handling_outgoing_price' => 5000,
                'handling_airplane_outgoing_price' => 3000,
                'handling_airplane_incoming_price' => 3000,
                'jppgc_incoming_price' => 10000,
                'jppgc_outgoing_price' => 10000,
            ],
            [
                'name' => 'WINGS AIR',
                'code' => 'IW',
                'cargo_handling_incoming_price' => 5000,
                'cargo_handling_outgoing_price' => 5000,
                'handling_airplane_outgoing_price' => 3000,
                'handling_airplane_incoming_price' => 3000,
                'jppgc_incoming_price' => 10000,
                'jppgc_outgoing_price' => 10000,
            ],
            [
                'name' => 'TRIGANA AIR',
                'code' => 'IL',
                'cargo_handling_incoming_price' => 5000,
                'cargo_handling_outgoing_price' => 5000,
                'handling_airplane_outgoing_price' => 3000,
                'handling_airplane_incoming_price' => 3000,
                'jppgc_incoming_price' => 10000,
                'jppgc_outgoing_price' => 10000,
            ],
            [
                'name' => 'HERCULES',
                'code' => 'C-130',
                'cargo_handling_incoming_price' => 5000,
                'cargo_handling_outgoing_price' => 5000,
                'handling_airplane_outgoing_price' => 3000,
                'handling_airplane_incoming_price' => 3000,
                'jppgc_incoming_price' => 10000,
                'jppgc_outgoing_price' => 10000,
            ],
            [
                'name' => 'CARTER',
                'code' => 'CHARTER',
                'cargo_handling_incoming_price' => 5000,
                'cargo_handling_outgoing_price' => 5000,
                'handling_airplane_outgoing_price' => 3000,
                'handling_airplane_incoming_price' => 3000,
                'jppgc_incoming_price' => 10000,
                'jppgc_outgoing_price' => 10000,
            ],
        ];

        foreach ($airlines as $airline) {
            Airline::updateOrCreate(['code' => $airline['code']], $airline);
        }
    }
}
