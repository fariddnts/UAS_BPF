<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BookingController extends Controller
{
    // Dipakai role user: memesan jadwal
    public function store(Request $request)
    {
        $data = $request->validate([
            'plat_nomor'      => ['required', 'string', 'max:15'],
            'jenis_motor'     => ['required', 'string', 'max:100'],
            'jenis_layanan'   => ['required', Rule::in(array_keys(Booking::LAYANAN))],
            'keluhan'         => ['nullable', 'string', 'max:500'],
            'tanggal_booking' => ['required', 'date', 'after_or_equal:today'],
            'jam_booking'     => ['required', 'date_format:H:i', 'after_or_equal:08:00', 'before_or_equal:16:00'],
        ]);

        $penuh = Booking::whereDate('tanggal_booking', $data['tanggal_booking'])
            ->where('jam_booking', $data['jam_booking'])
            ->count() >= Booking::KAPASITAS_PER_JAM;

        if ($penuh) {
            return back()
                ->withInput()
                ->withErrors(['jam_booking' => 'Jam tersebut sudah penuh, silakan pilih jam lain.']);
        }

        Booking::create($data + ['user_id' => $request->user()->id]);

        return redirect()->route('dashboard')->with('success', 'Jadwal berhasil dipesan.');
    }

    // Dipakai role mekanik: mengubah status pengerjaan
    public function updateStatus(Request $request, Booking $booking)
    {
        $request->validate([
            'status' => ['required', Rule::in(array_keys(Booking::STATUSES))],
        ]);

        $booking->update([
            'status'     => $request->status,
            'mekanik_id' => $request->user()->id,
        ]);

        return back()->with('success', 'Status pengerjaan diperbarui.');
    }
}
