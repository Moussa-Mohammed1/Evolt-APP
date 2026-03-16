<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChargingSession extends Model
{
    /** @use HasFactory<\Database\Factories\ChargingSessionFactory> */
    use HasFactory;

    protected $table = 'ChargingSessions';

    protected $fillable = [
        'ttl_energy_delivered',
        'status',
        'reservation_id',
    ];

    protected function casts(): array
    {
        return [
            'ttl_energy_delivered' => 'decimal:2',
        ];
    }

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }
}