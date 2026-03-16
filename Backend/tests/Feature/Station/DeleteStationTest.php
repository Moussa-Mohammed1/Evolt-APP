<?php

namespace Tests\Feature\Station;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Hash;
class DeleteStationTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_admin_cannot_delete_station_with_reservations()
    {
        $admin = \App\Models\User::create([
            'name' => 'admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

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

        $response = $this->actingAs($admin, 'sanctum')
            ->deleteJson('/api/stations/' . $station->id);

        $response->assertStatus(409)
            ->assertJsonFragment([
                'message' => 'Station cannot be deleted while reservations exist.',
            ]);
    }

    public function test_admin_can_delete_station_without_reservations()
    {
        $admin = \App\Models\User::create([
            'name' => 'admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        $station = \App\Models\Station::create([
            'name' => 'Station Safi',
            'zone_geographique' => 'Safi',
            'connector_type' => 'Type 2',
            'puissance_kw' => 23.44,
        ]);

        $response = $this->actingAs($admin, 'sanctum')
            ->deleteJson('/api/stations/' . $station->id);

        $response->assertStatus(204);

        $this->assertDatabaseMissing('stations', [
            'id' => $station->id,
        ]);
    }

    public function test_normal_user_cannot_delete_station()
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

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson('/api/stations/' . $station->id);

        $response->assertStatus(403);
    }
}
