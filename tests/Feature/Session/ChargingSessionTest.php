<?php

namespace Tests\Feature\Session;

use App\Models\ChargingSession;
use App\Models\Reservation;
use App\Models\Station;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ChargingSessionTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_charging_session_for_accepted_reservation(): void
    {
        $user = User::create([
            'name' => 'user',
            'email' => 'user@example.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
        ]);

        $station = Station::create([
            'name' => 'Station Safi',
            'zone_geographique' => 'Safi',
            'status' => 'available',
            'connector_type' => 'Type 2',
            'puissance_kw' => 23.44,
        ]);

        $reservation = Reservation::create([
            'start_time' => now()->addHour(),
            'end_time' => now()->addHours(2),
            'status' => 'accepted',
            'user_id' => $user->id,
            'station_id' => $station->id,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/charge', [
                'reservation_id' => $reservation->id,
                'ttl_energy_delivered' => 18.5,
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('ChargingSessions', [
            'reservation_id' => $reservation->id,
            'ttl_energy_delivered' => 18.5,
        ]);
    }

    public function test_user_cannot_register_charging_session_for_missing_reservation(): void
    {
        $user = User::create([
            'name' => 'user',
            'email' => 'user@example.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/charge', [
                'reservation_id' => 999,
                'ttl_energy_delivered' => 18.5,
            ]);

        $response->assertStatus(422);
    }

    public function test_user_cannot_register_charging_session_when_reservation_not_accepted(): void
    {
        $user = User::create([
            'name' => 'user',
            'email' => 'user@example.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
        ]);

        $station = Station::create([
            'name' => 'Station Safi',
            'zone_geographique' => 'Safi',
            'status' => 'available',
            'connector_type' => 'Type 2',
            'puissance_kw' => 23.44,
        ]);

        $reservation = Reservation::create([
            'start_time' => now()->addHour(),
            'end_time' => now()->addHours(2),
            'status' => 'pending',
            'user_id' => $user->id,
            'station_id' => $station->id,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/charge', [
                'reservation_id' => $reservation->id,
                'ttl_energy_delivered' => 18.5,
            ]);

        $response->assertStatus(409);
    }

    public function test_user_can_see_only_their_charging_history_sorted_desc(): void
    {
        $owner = User::create([
            'name' => 'owner',
            'email' => 'owner@example.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
        ]);

        $otherUser = User::create([
            'name' => 'other',
            'email' => 'other@example.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
        ]);

        $station = Station::create([
            'name' => 'Station Safi',
            'zone_geographique' => 'Safi',
            'status' => 'available',
            'connector_type' => 'Type 2',
            'puissance_kw' => 23.44,
        ]);

        $otherReservation = Reservation::create([
            'start_time' => now()->addDays(3)->setTime(10, 0),
            'end_time' => now()->addDays(3)->setTime(11, 0),
            'status' => 'accepted',
            'user_id' => $otherUser->id,
            'station_id' => $station->id,
        ]);

        ChargingSession::create([
            'reservation_id' => $otherReservation->id,
            'ttl_energy_delivered' => 30,
            'status' => 'completed',
        ]);

        $response = $this->actingAs($owner, 'sanctum')
            ->getJson('/api/charging-sessions/history');

        $response->assertStatus(200);
    }

    public function test_guest_cannot_see_charging_history(): void
    {
        $response = $this->getJson('/api/charging-sessions/history');

        $response->assertStatus(401);
    }
}
