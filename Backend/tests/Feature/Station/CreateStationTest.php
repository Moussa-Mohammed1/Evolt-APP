<?php

namespace Tests\Feature\Station;

use Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CreateStationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     */
    public function test_admin_can_create_station(): void
    {
        $admin = \App\Models\User::create([
            'name' => 'moussa',
            'email' => 'moussa@gmail.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/stations', [
                'name' => 'Station Safi',
                'zone_geographique' => 'Safi',
                'connector_type' => 'Type 2',
                'puissance_kw' => 23.44,
            ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('stations', [
            'name' => 'Station Safi',
        ]);
    }

    public function test_normal_user_cannot_create_station()
    {
        $user = \App\Models\User::create([
            'name' => 'moussa',
            'email' => 'moussa@gmail.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/stations', [
                'name' => 'Station Safi',
                'zone_geographique' => 'Safi',
                'connector_type' => 'Type 2',
                'puissance_kw' => 23.44,
            ]);
        $response->assertStatus(403);
    }

    public function test_admin_cannot_create_station_with_missing_fields()
    {
        $admin = \App\Models\User::create([
            'name' => 'moussa',
            'email' => 'moussa@gmail.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/stations', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'name',
                'zone_geographique',
                'connector_type',
                'puissance_kw',
            ]);
    }
}
