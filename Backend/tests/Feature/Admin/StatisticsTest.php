<?php

namespace Tests\Feature\Admin;

use App\Models\ChargingSession;
use App\Models\Reservation;
use App\Models\Station;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StatisticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_global_stats(): void
    {
        $admin = User::create([
            'name' => 'admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        $station1 = Station::create([
            'name' => 'Station Safi',
            'zone_geographique' => 'Safi',
            'status' => 'available',
            'connector_type' => 'Type 2',
            'puissance_kw' => 23.44,
        ]);

        $station2 = Station::create([
            'name' => 'Station Casa',
            'zone_geographique' => 'Casablanca',
            'status' => 'unavailable',
            'connector_type' => 'CCS',
            'puissance_kw' => 50.00,
        ]);

        $user = User::create([
            'name' => 'user',
            'email' => 'user@example.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
        ]);

        $acceptedReservation = Reservation::create([
            'start_time' => now()->addDay()->setTime(10, 0),
            'end_time' => now()->addDay()->setTime(11, 0),
            'status' => 'accepted',
            'user_id' => $user->id,
            'station_id' => $station1->id,
        ]);

        Reservation::create([
            'start_time' => now()->addDays(2)->setTime(10, 0),
            'end_time' => now()->addDays(2)->setTime(11, 0),
            'status' => 'pending',
            'user_id' => $user->id,
            'station_id' => $station2->id,
        ]);

        ChargingSession::create([
            'reservation_id' => $acceptedReservation->id,
            'ttl_energy_delivered' => 12.5,
            'status' => 'completed',
        ]);

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/admin/stats');

        $response->assertStatus(200);
    }

    public function test_non_admin_cannot_view_global_stats(): void
    {
        $user = User::create([
            'name' => 'user',
            'email' => 'user@example.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/admin/stats');

        $response->assertStatus(403);
    }

    public function test_admin_can_view_station_stats(): void
    {
        $admin = User::create([
            'name' => 'admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        $user = User::create([
            'name' => 'user',
            'email' => 'user@example.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
        ]);

        $station = Station::create([
            'name' => 'Station Rabat',
            'zone_geographique' => 'Rabat',
            'status' => 'available',
            'connector_type' => 'Type 2',
            'puissance_kw' => 22,
        ]);

        $acceptedReservation = Reservation::create([
            'start_time' => now()->addDay()->setTime(9, 0),
            'end_time' => now()->addDay()->setTime(10, 0),
            'status' => 'accepted',
            'user_id' => $user->id,
            'station_id' => $station->id,
        ]);

        Reservation::create([
            'start_time' => now()->addDay()->setTime(11, 0),
            'end_time' => now()->addDay()->setTime(12, 0),
            'status' => 'pending',
            'user_id' => $user->id,
            'station_id' => $station->id,
        ]);

        ChargingSession::create([
            'reservation_id' => $acceptedReservation->id,
            'ttl_energy_delivered' => 8.75,
            'status' => 'completed',
        ]);

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/stations/' . $station->id . '/stats');

        $response->assertStatus(200);
    }

    public function test_non_admin_cannot_view_station_stats(): void
    {
        $user = User::create([
            'name' => 'user',
            'email' => 'user@example.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
        ]);

        $station = Station::create([
            'name' => 'Station Rabat',
            'zone_geographique' => 'Rabat',
            'status' => 'available',
            'connector_type' => 'Type 2',
            'puissance_kw' => 22,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/stations/' . $station->id . '/stats');

        $response->assertStatus(403);
    }
}
