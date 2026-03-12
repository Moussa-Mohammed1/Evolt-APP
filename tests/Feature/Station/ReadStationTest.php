<?php

namespace Tests\Feature\Station;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Hash;
class ReadStationTest extends TestCase
{

    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_guest_can_get_stations_list()
    {
        \App\Models\Station::create([
            'name' => 'Station Safi',
            'zone_geographique' => 'Safi',
            'connector_type' => 'Type 2',
            'puissance_kw' => 23.44,
        ]);

        \App\Models\Station::create([
            'name' => 'Station Casa',
            'zone_geographique' => 'Casablanca',
            'connector_type' => 'CCS',
            'puissance_kw' => 50,
        ]);

        $response = $this->getJson('/api/stations');

        $response->assertStatus(200)
            ->assertJsonCount(2)
            ->assertJsonFragment([
                'name' => 'Station Safi',
            ]);
    }

    public function test_authenticated_user_can_show_station()
    {
        $user = \App\Models\User::create([
            'name' => 'moussa',
            'email' => 'moussa@gmail.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
        ]);

        $station = \App\Models\Station::create([
            'name' => 'Station Safi',
            'zone_geographique' => 'Safi',
            'connector_type' => 'Type 2',
            'puissance_kw' => 23.44,
        ]);

        \App\Models\Reservation::create([
            'start_time' => now(),
            'end_time' => now()->addHour(),
            'status' => 'pending',
            'user_id' => $user->id,
            'station_id' => $station->id,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/stations/' . $station->id);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'id' => $station->id,
                'name' => 'Station Safi',
                'reservations_count' => 1,
            ]);
    }
}
