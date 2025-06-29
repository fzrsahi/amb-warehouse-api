<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Flight;
use App\Models\Airline;
use App\Models\Location;
use Carbon\Carbon;

class FlightSeeder extends Seeder
{
    public function run()
    {
        // Get all airlines and locations
        $airlines = Airline::all();
        $locations = Location::all();

        // Define 10 specific flights
        $flights = [
            // Flight 1: GTO to CGK (Garuda)
            [
                'origin_code' => 'GTO',
                'destination_code' => 'CGK',
                'airline_code' => 'GA',
                'flight_date' => Carbon::now()->addDays(1),
                'departure_time' => '08:30',
                'status' => 'outgoing'
            ],

            // Flight 2: CGK to GTO (Lion Air)
            [
                'origin_code' => 'CGK',
                'destination_code' => 'GTO',
                'airline_code' => 'JT',
                'flight_date' => Carbon::now()->addDays(1),
                'departure_time' => '14:15',
                'status' => 'incoming'
            ],

            // Flight 3: GTO to UPG (Batik Air)
            [
                'origin_code' => 'GTO',
                'destination_code' => 'UPG',
                'airline_code' => 'ID',
                'flight_date' => Carbon::now()->addDays(2),
                'departure_time' => '10:00',
                'status' => 'outgoing'
            ],

            // Flight 4: UPG to GTO (Garuda)
            [
                'origin_code' => 'UPG',
                'destination_code' => 'GTO',
                'airline_code' => 'GA',
                'flight_date' => Carbon::now()->addDays(2),
                'departure_time' => '16:45',
                'status' => 'incoming'
            ],

            // Flight 5: GTO to MDC (Lion Air)
            [
                'origin_code' => 'GTO',
                'destination_code' => 'MDC',
                'airline_code' => 'JT',
                'flight_date' => Carbon::now()->addDays(3),
                'departure_time' => '09:30',
                'status' => 'outgoing'
            ],

            // Flight 6: MDC to GTO (Batik Air)
            [
                'origin_code' => 'MDC',
                'destination_code' => 'GTO',
                'airline_code' => 'ID',
                'flight_date' => Carbon::now()->addDays(3),
                'departure_time' => '15:20',
                'status' => 'incoming'
            ],

            // Flight 7: GTO to HLM (Garuda)
            [
                'origin_code' => 'GTO',
                'destination_code' => 'HLM',
                'airline_code' => 'GA',
                'flight_date' => Carbon::now()->addDays(4),
                'departure_time' => '11:00',
                'status' => 'outgoing'
            ],

            // Flight 8: HLM to GTO (Lion Air)
            [
                'origin_code' => 'HLM',
                'destination_code' => 'GTO',
                'airline_code' => 'JT',
                'flight_date' => Carbon::now()->addDays(4),
                'departure_time' => '17:30',
                'status' => 'incoming'
            ],

            // Flight 9: Charter flight GTO to CUSTOM
            [
                'origin_code' => 'GTO',
                'destination_code' => 'CUSTOM',
                'airline_code' => 'CHARTER',
                'flight_date' => Carbon::now()->addDays(5),
                'departure_time' => '12:00',
                'status' => 'outgoing'
            ],

            // Flight 10: Hercules military flight GTO to CGK
            [
                'origin_code' => 'GTO',
                'destination_code' => 'CGK',
                'airline_code' => 'C-130',
                'flight_date' => Carbon::now()->addDays(6),
                'departure_time' => '07:00',
                'status' => 'outgoing'
            ],
        ];

        foreach ($flights as $flightData) {
            $originLocation = $locations->where('code', $flightData['origin_code'])->first();
            $destinationLocation = $locations->where('code', $flightData['destination_code'])->first();
            $airline = $airlines->where('code', $flightData['airline_code'])->first();

            if (!$originLocation || !$destinationLocation || !$airline) {
                continue;
            }

            $departureAt = $flightData['flight_date']->copy()->setTimeFromTimeString($flightData['departure_time']);

            // Calculate arrival time (1-3 hours later)
            $flightDuration = rand(1, 3);
            $arrivalAt = $departureAt->copy()->addHours($flightDuration);

            Flight::updateOrCreate(
                [
                    'origin_id' => $originLocation->id,
                    'destination_id' => $destinationLocation->id,
                    'airline_id' => $airline->id,
                    'flight_date' => $flightData['flight_date']->format('Y-m-d'),
                    'departure_at' => $departureAt->format('Y-m-d H:i:s'),
                ],
                [
                    'status' => $flightData['status'],
                    'arrival_at' => $arrivalAt->format('Y-m-d H:i:s'),
                ]
            );
        }
    }
}
