<?php

namespace Tests\Feature\Station;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Hash;

class UpdateStationTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    use RefreshDatabase;
    public function test_admin_can_update_station()
    {
        $admin = \App\Models\User::create([
            'name' => 'moussa',
            'email' => 'moussa@gmail.com',
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
            ->patchJson('/api/stations/' . $station->id, [
                'status' => 'unavailable',
            ]);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'status' => 'unavailable',
            ]);

        $this->assertDatabaseHas('stations', [
            'id' => $station->id,
            'status' => 'unavailable',
        ]);
    }

    public function test_normal_user_cannot_update_station()
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
            ->patchJson('/api/stations/' . $station->id, [
                'status' => 'unavailable',
            ]);

        $response->assertStatus(403);
    }
}
