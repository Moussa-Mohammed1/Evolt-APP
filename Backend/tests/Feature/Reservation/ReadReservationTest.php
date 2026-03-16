<?php

namespace Tests\Feature\Reservation;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ReadReservationTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_get_only_their_reservations(): void
    {
        $user = \App\Models\User::create([
            'name' => 'moussa',
            'email' => 'moussa@gmail.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
        ]);

        $otherUser = \App\Models\User::create([
            'name' => 'salma',
            'email' => 'salma@gmail.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
        ]);

        $stationSafi = \App\Models\Station::create([
            'name' => 'Station Safi',
            'zone_geographique' => 'Safi',
            'status' => 'available',
            'connector_type' => 'Type 2',
            'puissance_kw' => 23.44,
        ]);

        $stationCasa = \App\Models\Station::create([
            'name' => 'Station Casa',
            'zone_geographique' => 'Casablanca',
            'status' => 'available',
            'connector_type' => 'CCS',
            'puissance_kw' => 50.00,
        ]);

        $reservation = \App\Models\Reservation::create([
            'start_time' => now()->addDay()->setTime(10, 0),
            'end_time' => now()->addDay()->setTime(11, 0),
            'status' => 'pending',
            'user_id' => $user->id,
            'station_id' => $stationSafi->id,
        ]);

        \App\Models\Reservation::create([
            'start_time' => now()->addDays(2)->setTime(10, 0),
            'end_time' => now()->addDays(2)->setTime(11, 0),
            'status' => 'accepted',
            'user_id' => $otherUser->id,
            'station_id' => $stationCasa->id,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/reservations');

        $response->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', $reservation->id)
            ->assertJsonPath('0.station.name', 'Station Safi')
            ;
    }

    public function test_guest_cannot_get_reservations_list(): void
    {
        $response = $this->getJson('/api/reservations');

        $response->assertStatus(401);
    }
}
