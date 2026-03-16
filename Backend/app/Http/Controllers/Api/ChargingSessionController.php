<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreChargingSessionRequest;
use App\Models\ChargingSession;
use App\Models\Reservation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChargingSessionController extends Controller
{
    public function store(StoreChargingSessionRequest $request)
    {
        $reservation = Reservation::find($request->reservation_id);
        if (!$reservation) {
            return response()->json([
                'message' => 'no reservation found'
            ], 404);
        }
        if ($reservation->status !== 'accepted') {
            return response()->json([
                'message' => 'reservation not accepted yet'
            ], 409);
        }
        $charge = ChargingSession::create($request->validated());
        return response()->json([
            'message' => 'Charged session registered successfully',
            'session_info' => $charge
        ]);

    }
    public function history(Request $request): JsonResponse
    {
        $todayStart = now()->startOfDay();

        $sessions = ChargingSession::query()
            ->whereHas('reservation', function ($query) use ($request) {
                $query->where('user_id', $request->user()->id)
                    ->where('start_time', '<=', now()->endOfDay());
            })
            ->with(['reservation.station'])
            ->get()
            ->sortByDesc(static fn (ChargingSession $session): int =>
                $session->reservation?->start_time?->getTimestamp() ?? 0
            )
            ->values();

        $currentSessions = $sessions
            ->filter(static fn (ChargingSession $session): bool =>
                $session->reservation?->start_time?->isToday() ?? false
            )
            ->values();

        $pastSessions = $sessions
            ->filter(static fn (ChargingSession $session): bool =>
                $session->reservation?->start_time?->lt($todayStart) ?? false
            )
            ->values();

        return response()->json([
            'current_sessions' => $currentSessions,
            'past_sessions' => $pastSessions,
            'meta' => [
                'total_sessions' => $currentSessions->count() + $pastSessions->count(),
                'current_sessions_count' => $currentSessions->count(),
                'past_sessions_count' => $pastSessions->count(),
            ],
        ]);
    }
}