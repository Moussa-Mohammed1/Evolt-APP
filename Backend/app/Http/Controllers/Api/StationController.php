<?php

namespace App\Http\Controllers\Api;

use App\Models\ChargingSession;
use App\Models\Reservation;
use App\Models\Station;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStationRequest;
use App\Http\Requests\UpdateStationRequest;
use Illuminate\Http\JsonResponse;

class StationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $stations = Station::all();
        return response()->json($stations);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreStationRequest $request)
    {
        $station = Station::create($request->validated());

        return response()->json($station, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Station $station)
    {
        $station->loadCount('reservations');

        return response()->json($station);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateStationRequest $request, Station $station)
    {
        $station->update($request->validated());

        return response()->json($station->fresh());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Station $station)
    {
        if ($station->reservations()->exists()) {
            return response()->json([
                'message' => 'Station cannot be deleted while reservations exist.',
            ], 409);
        }

        $station->delete();

        return response()->json(['message' => 'Status delete successfully'], status: 204);
    }

    /**
     * Global statistics across all stations (admin only).
     */
    public function globalStats(): JsonResponse
    {
        $totalStations = Station::count();

        $stationsByStatus = Station::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $totalReservations = Reservation::count();

        $reservationsByStatus = Reservation::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $totalEnergy = (float) ChargingSession::sum('ttl_energy_delivered');
        $totalSessions = ChargingSession::count();

        return response()->json([
            'total_stations'             => $totalStations,
            'stations_by_status'         => $stationsByStatus,
            'total_reservations'         => $totalReservations,
            'reservations_by_status'     => $reservationsByStatus,
            'total_energy_delivered_kwh' => $totalEnergy,
            'total_charging_sessions'    => $totalSessions,
        ]);
    }

    /**
     * Statistics for a single station (admin only).
     */
    public function stats(Station $station): JsonResponse
    {
        $totalReservations = $station->reservations()->count();

        $reservationsByStatus = $station->reservations()
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $acceptedCount = (int) ($reservationsByStatus->get('accepted', 0));
        $occupancyRate = $totalReservations > 0
            ? round(($acceptedCount / $totalReservations) * 100, 2)
            : 0.0;

        $totalEnergy = (float) ChargingSession::whereHas(
            'reservation',
            fn ($q) => $q->where('station_id', $station->id)
        )->sum('ttl_energy_delivered');

        $totalSessions = ChargingSession::whereHas(
            'reservation',
            fn ($q) => $q->where('station_id', $station->id)
        )->count();

        return response()->json([
            'station' => $station->only([
                'id', 'name', 'zone_geographique', 'status', 'connector_type', 'puissance_kw',
            ]),
            'total_reservations'         => $totalReservations,
            'reservations_by_status'     => $reservationsByStatus,
            'occupancy_rate_percent'     => $occupancyRate,
            'total_charging_sessions'    => $totalSessions,
            'total_energy_delivered_kwh' => $totalEnergy,
        ]);
    }
}
