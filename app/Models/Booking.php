<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    public const STATUSES = [
        'pending'     => 'Pending',
        'on_progress' => 'On Progress',
        'finished'    => 'Finished',
    ];

    public const LAYANAN = [
        'servis_rutin'    => 'Servis Rutin',
        'ganti_oli'       => 'Ganti Oli',
        'ganti_sparepart' => 'Ganti Sparepart',
        'lainnya'         => 'Lainnya',
    ];

    public const KAPASITAS_PER_JAM = 3;

    protected $fillable = [
        'user_id', 'mekanik_id', 'plat_nomor', 'jenis_motor', 'jenis_layanan',
        'keluhan', 'tanggal_booking', 'jam_booking', 'status',
    ];

    protected function casts(): array
    {
        return ['tanggal_booking' => 'date'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function mekanik(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mekanik_id');
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function badgeClass(): string
    {
        return match ($this->status) {
            'on_progress' => 'bg-info',
            'finished'    => 'bg-success',
            default       => 'bg-warning',
        };
    }
}
