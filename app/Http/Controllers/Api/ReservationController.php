<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReservationRequest;
use App\Http\Requests\UpdateReservationRequest;
use App\Jobs\ReleaseStationAfterReservation;
use App\Models\Reservation;
use App\Models\Station;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $reservations = $request->user()
            ->reservations()
            ->with('station')
            ->orderBy('start_time')
            ->get();

        return response()->json($reservations);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreReservationRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $station = Station::query()->findOrFail($validated['station_id']);

        if ($station->status !== 'available') {
            return response()->json([
                'message' => 'This station is not available for reservation.',
            ], 409);
        }

        $hasConflict = $station->reservations()
            ->whereNotIn('status', ['cancelled', 'expired'])
            ->where('start_time', '<', $validated['end_time'])
            ->where('end_time', '>', $validated['start_time'])
            ->exists();

        if ($hasConflict) {
            return response()->json([
                'message' => 'This station is already reserved for the selected time slot.',
            ], 409);
        }

        $reservation = $request->user()->reservations()->create([
            'station_id' => $station->id,
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'status' => 'pending',
        ]);

        ReleaseStationAfterReservation::dispatch($reservation->id)
            ->delay($reservation->end_time);

        return response()->json([
            'message' => 'Reservation created successfully.',
            'reservation' => $reservation->load(['station', 'user']),
        ], 201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateReservationRequest $request, Reservation $reservation): JsonResponse
    {
        if ($reservation->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Reservation not found.',
            ], 404);
        }

        if ($reservation->status === 'cancelled') {
            return response()->json([
                'message' => 'Cancelled reservations cannot be updated.',
            ], 409);
        }

        $validated = $request->validated();
        $newStatus = $validated['status'] ?? $reservation->status;
        $newStart = $validated['start_time'] ?? $reservation->start_time->toDateTimeString();
        $newEnd = $validated['end_time'] ?? $reservation->end_time->toDateTimeString();

        if (strtotime($newEnd) <= strtotime($newStart)) {
            return response()->json([
                'message' => 'The end_time must be after start_time.',
            ], 422);
        }

        $station = Station::query()->findOrFail($reservation->station_id);

        if ($station->status !== 'available' && $reservation->status !== 'accepted') {
            return response()->json([
                'message' => 'This station is not available for reservation.',
            ], 409);
        }

        $hasConflict = $station->reservations()
            ->whereKeyNot($reservation->id)
            ->whereNotIn('status', ['cancelled', 'expired'])
            ->where('start_time', '<', $newEnd)
            ->where('end_time', '>', $newStart)
            ->exists();

        if ($hasConflict) {
            return response()->json([
                'message' => 'This station is already reserved for the selected time slot.',
            ], 409);
        }

        $reservation->update([
            'start_time' => $newStart,
            'end_time' => $newEnd,
            'status' => $newStatus,
        ]);

        $this->syncStationAvailability($station);

        $reservation = $reservation->fresh();

        if (!in_array($reservation->status, ['cancelled', 'expired'], true)) {
            ReleaseStationAfterReservation::dispatch($reservation->id)
                ->delay($reservation->end_time);
        }

        return response()->json([
            'message' => 'Reservation updated successfully.',
            'reservation' => $reservation->load(['station', 'user']),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Reservation $reservation): JsonResponse
    {
        if ($reservation->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Reservation not found.',
            ], 404);
        }

        if ($reservation->status === 'cancelled') {
            return response()->json([
                'message' => 'Reservation is already cancelled.',
            ], 422);
        }

        $reservation->update([
            'status' => 'cancelled',
        ]);

        $station = Station::query()->findOrFail($reservation->station_id);
        $this->syncStationAvailability($station);

        return response()->json([
            'message' => 'Reservation cancelled successfully.',
            'reservation' => $reservation->fresh()->load(['station', 'user']),
        ]);
    }

    private function syncStationAvailability(Station $station): void
    {
        $hasActiveAcceptedReservation = $station->reservations()
            ->where('status', 'accepted')
            ->where('end_time', '>', now())
            ->exists();

        $station->update([
            'status' => $hasActiveAcceptedReservation ? 'unavailable' : 'available',
        ]);
    }
}
