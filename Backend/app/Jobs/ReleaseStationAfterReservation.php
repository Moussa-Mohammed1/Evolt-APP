<?php

namespace App\Jobs;

use App\Models\Reservation;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class ReleaseStationAfterReservation implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public int $reservationId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $reservationId)
    {
        $this->reservationId = $reservationId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $reservation = Reservation::query()->with('station')->find($this->reservationId);

        if (!$reservation) {
            return;
        }

        if (in_array($reservation->status, ['cancelled', 'expired'], true)) {
            return;
        }

        if (now()->lt($reservation->end_time)) {
            $secondsUntilEnd = max(1, Carbon::now()->diffInSeconds($reservation->end_time, false));
            $this->release($secondsUntilEnd);
            return;
        }

        $reservation->update(['status' => 'expired']);

        $station = $reservation->station;

        if (!$station) {
            return;
        }

        $hasActiveAcceptedReservation = $station->reservations()
            ->where('status', 'accepted')
            ->where('end_time', '>', now())
            ->exists();

        if (!$hasActiveAcceptedReservation) {
            $station->update(['status' => 'available']);
        }
    }
}
