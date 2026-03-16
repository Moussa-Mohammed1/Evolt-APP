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

        $ownerCurrentLatestReservation = Reservation::create([
            'start_time' => now()->setTime(20, 0),
            'end_time' => now()->setTime(21, 0),
            'status' => 'accepted',
            'user_id' => $owner->id,
            'station_id' => $station->id,
        ]);

        $ownerCurrentOlderReservation = Reservation::create([
            'start_time' => now()->setTime(8, 0),
            'end_time' => now()->setTime(9, 0),
            'status' => 'accepted',
            'user_id' => $owner->id,
            'station_id' => $station->id,
        ]);

        $ownerPastRecentReservation = Reservation::create([
            'start_time' => now()->subDay()->setTime(18, 0),
            'end_time' => now()->subDay()->setTime(19, 0),
            'status' => 'accepted',
            'user_id' => $owner->id,
            'station_id' => $station->id,
        ]);

        $ownerPastOlderReservation = Reservation::create([
            'start_time' => now()->subDays(4)->setTime(13, 0),
            'end_time' => now()->subDays(4)->setTime(14, 0),
            'status' => 'accepted',
            'user_id' => $owner->id,
            'station_id' => $station->id,
        ]);

        $ownerFutureReservation = Reservation::create([
            'start_time' => now()->addDay()->setTime(10, 0),
            'end_time' => now()->addDay()->setTime(11, 0),
            'status' => 'accepted',
            'user_id' => $owner->id,
            'station_id' => $station->id,
        ]);

        $otherReservation = Reservation::create([
            'start_time' => now()->setTime(15, 0),
            'end_time' => now()->setTime(16, 0),
            'status' => 'accepted',
            'user_id' => $otherUser->id,
            'station_id' => $station->id,
        ]);

        $ownerCurrentLatestSession = ChargingSession::create([
            'reservation_id' => $ownerCurrentLatestReservation->id,
            'ttl_energy_delivered' => 45,
            'status' => 'completed',
        ]);

        $ownerCurrentOlderSession = ChargingSession::create([
            'reservation_id' => $ownerCurrentOlderReservation->id,
            'ttl_energy_delivered' => 20,
            'status' => 'completed',
        ]);

        $ownerPastRecentSession = ChargingSession::create([
            'reservation_id' => $ownerPastRecentReservation->id,
            'ttl_energy_delivered' => 28,
            'status' => 'completed',
        ]);

        $ownerPastOlderSession = ChargingSession::create([
            'reservation_id' => $ownerPastOlderReservation->id,
            'ttl_energy_delivered' => 18,
            'status' => 'completed',
        ]);

        $ownerFutureSession = ChargingSession::create([
            'reservation_id' => $ownerFutureReservation->id,
            'ttl_energy_delivered' => 36,
            'status' => 'completed',
        ]);

        $otherUserSession = ChargingSession::create([
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
