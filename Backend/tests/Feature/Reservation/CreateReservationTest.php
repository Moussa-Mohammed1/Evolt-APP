<?php

namespace Tests\Feature\Reservation;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CreateReservationTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_reservation(): void
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
            'status' => 'available',
            'connector_type' => 'Type 2',
            'puissance_kw' => 23.44,
        ]);

        $startTime = now()->addDay()->setTime(10, 0);
        $endTime = $startTime->copy()->addHour();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/reservations', [
                'station_id' => $station->id,
                'start_time' => $startTime->toDateTimeString(),
                'end_time' => $endTime->toDateTimeString(),
            ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('reservations', [
            'user_id' => $user->id,
            'station_id' => $station->id,
            'status' => 'pending',
        ]);
    }

    public function test_user_cannot_create_reservation_for_unavailable_station(): void
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
            'status' => 'unavailable',
            'connector_type' => 'Type 2',
            'puissance_kw' => 23.44,
        ]);

        $startTime = now()->addDay()->setTime(10, 0);
        $endTime = (clone $startTime)->addHour();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/reservations', [
                'station_id' => $station->id,
                'start_time' => $startTime->toDateTimeString(),
                'end_time' => $endTime->toDateTimeString(),
            ]);

        $response->assertStatus(409)
            ->assertJsonFragment([
                'message' => 'This station is not available for reservation.',
            ]);
    }

    public function test_user_cannot_create_reservation_with_conflicting_time_slot(): void
    {
        $firstUser = \App\Models\User::create([
            'name' => 'moussa',
            'email' => 'moussa@gmail.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
        ]);

        $secondUser = \App\Models\User::create([
            'name' => 'salma',
            'email' => 'salma@gmail.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
        ]);

        $station = \App\Models\Station::create([
            'name' => 'Station Safi',
            'zone_geographique' => 'Safi',
            'status' => 'available',
            'connector_type' => 'Type 2',
            'puissance_kw' => 23.44,
        ]);

        $existingStart = now()->addDay()->setTime(10, 0);
        $existingEnd = $existingStart->copy()->addHour();

        \App\Models\Reservation::create([
            'start_time' => $existingStart->toDateTimeString(),
            'end_time' => $existingEnd->toDateTimeString(),
            'status' => 'pending',
            'user_id' => $firstUser->id,
            'station_id' => $station->id,
        ]);

        $response = $this->actingAs($secondUser, 'sanctum')
            ->postJson('/api/reservations', [
                'station_id' => $station->id,
                'start_time' => $existingStart->copy()->addMinutes(30)->toDateTimeString(),
                'status' => 'pending',
                'end_time' => $existingEnd->copy()->addMinutes(30)->toDateTimeString(),
            ]);

        $response->assertStatus(409);
    }

    public function test_user_cannot_create_reservation_with_missing_fields(): void
    {
        $user = \App\Models\User::create([
            'name' => 'moussa',
            'email' => 'moussa@gmail.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/reservations', []);

        $response->assertStatus(422);
    }
}
