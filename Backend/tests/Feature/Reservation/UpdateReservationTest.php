<?php

namespace Tests\Feature\Reservation;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UpdateReservationTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_update_reservation(): void
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

        $reservation = \App\Models\Reservation::create([
            'start_time' => now()->addDay()->setTime(10, 0),
            'end_time' => now()->addDay()->setTime(11, 0),
            'status' => 'pending',
            'user_id' => $user->id,
            'station_id' => $station->id,
        ]);

        $newStart = now()->addDays(2)->setTime(14, 0);
        $newEnd = $newStart->copy()->addHour();

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson('/api/reservations/' . $reservation->id, [
                'start_time' => $newStart->toDateTimeString(),
                'end_time' => $newEnd->toDateTimeString(),
                'status' => 'accepted',
            ]);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'message' => 'Reservation updated successfully.',
                'status' => 'accepted',
            ]);

        $this->assertDatabaseHas('reservations', [
            'id' => $reservation->id,
            'status' => 'accepted',
            'start_time' => $newStart->toDateTimeString(),
            'end_time' => $newEnd->toDateTimeString(),
        ]);
    }

    public function test_user_cannot_update_another_users_reservation(): void
    {
        $owner = \App\Models\User::create([
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

        $station = \App\Models\Station::create([
            'name' => 'Station Safi',
            'zone_geographique' => 'Safi',
            'status' => 'available',
            'connector_type' => 'Type 2',
            'puissance_kw' => 23.44,
        ]);

        $reservation = \App\Models\Reservation::create([
            'start_time' => now()->addDay()->setTime(10, 0),
            'end_time' => now()->addDay()->setTime(11, 0),
            'status' => 'pending',
            'user_id' => $owner->id,
            'station_id' => $station->id,
        ]);

        $response = $this->actingAs($otherUser, 'sanctum')
            ->patchJson('/api/reservations/' . $reservation->id, [
                'start_time' => now()->addDays(2)->setTime(12, 0)->toDateTimeString(),
                'end_time' => now()->addDays(2)->setTime(13, 0)->toDateTimeString(),
                'status' => 'accepted',
            ]);

        $response->assertStatus(404);
    }

    public function test_user_cannot_update_cancelled_reservation(): void
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

        $reservation = \App\Models\Reservation::create([
            'start_time' => now()->addDay()->setTime(10, 0),
            'end_time' => now()->addDay()->setTime(11, 0),
            'status' => 'cancelled',
            'user_id' => $user->id,
            'station_id' => $station->id,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson('/api/reservations/' . $reservation->id, [
                'start_time' => now()->addDays(2)->setTime(12, 0)->toDateTimeString(),
                'end_time' => now()->addDays(2)->setTime(13, 0)->toDateTimeString(),
                'status' => 'accepted',
            ]);

        $response->assertStatus(409);
    }

    public function test_user_cannot_update_reservation_with_conflicting_time_slot(): void
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

        $station = \App\Models\Station::create([
            'name' => 'Station Safi',
            'zone_geographique' => 'Safi',
            'status' => 'available',
            'connector_type' => 'Type 2',
            'puissance_kw' => 23.44,
        ]);

        $reservation = \App\Models\Reservation::create([
            'start_time' => now()->addDay()->setTime(8, 0),
            'end_time' => now()->addDay()->setTime(9, 0),
            'status' => 'pending',
            'user_id' => $user->id,
            'station_id' => $station->id,
        ]);

        \App\Models\Reservation::create([
            'start_time' => now()->addDays(2)->setTime(10, 0),
            'end_time' => now()->addDays(2)->setTime(11, 0),
            'status' => 'accepted',
            'user_id' => $otherUser->id,
            'station_id' => $station->id,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson('/api/reservations/' . $reservation->id, [
                'start_time' => now()->addDays(2)->setTime(10, 30)->toDateTimeString(),
                'end_time' => now()->addDays(2)->setTime(11, 30)->toDateTimeString(),
                'status' => 'accepted',
            ]);

        $response->assertStatus(409);
    }
}
