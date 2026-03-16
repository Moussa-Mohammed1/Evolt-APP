<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Station extends Model
{
    /** @use HasFactory<\Database\Factories\StationFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'zone_geographique',
        'status',
        'connector_type',
        'puissance_kw',
    ];

    protected function casts(): array
    {
        return [
            'puissance_kw' => 'decimal:2',
        ];
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }
}
