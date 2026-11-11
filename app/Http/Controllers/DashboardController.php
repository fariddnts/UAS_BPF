<?php

namespace App\Http\Controllers;

use App\Models\Booking;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // User: form pesan jadwal dan riwayat pesanannya
        if ($user->hasRole('user')) {
            $bookings = Booking::where('user_id', $user->id)
                ->latest()
                ->get();

            return view('dashboard.user', compact('bookings'));
        }

        // Mekanik: daftar pekerjaan dan ubah status
        if ($user->hasRole('mekanik')) {
            $bookings = Booking::with('user')
                ->orderByRaw("FIELD(status, 'on_progress', 'pending', 'finished')")
                ->orderBy('tanggal_booking')
                ->orderBy('jam_booking')
                ->get();

            return view('dashboard.mekanik', compact('bookings'));
        }

        // Admin dan owner: dashboard yang sudah ada di resources/views/admin/dashboard.blade.php
        return view('admin.dashboard');
    }
}
