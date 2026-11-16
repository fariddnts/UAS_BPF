<?php

namespace App\Http\Controllers;

use App\Models\Booking;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // User: halaman beranda layanan
        if ($user->hasRole('user')) {
            return view('dashboard.user');
        }

        // Admin dan mekanik: daftar pekerjaan
        if ($user->hasRole('admin', 'mekanik')) {
            $bookings = Booking::with('user')
                ->orderByRaw("FIELD(status, 'on_progress', 'pending', 'finished')")
                ->orderBy('tanggal_booking')
                ->orderBy('jam_booking')
                ->get();

            return view('dashboard.mekanik', compact('bookings'));
        }

        $now = now();
        $periods = [
            'daily' => [
                'label' => 'Hari Ini',
                'start' => $now->copy()->startOfDay(),
                'end' => $now->copy()->endOfDay(),
            ],
            'monthly' => [
                'label' => 'Bulan Ini',
                'start' => $now->copy()->startOfMonth(),
                'end' => $now->copy()->endOfMonth(),
            ],
            'yearly' => [
                'label' => 'Tahun Ini',
                'start' => $now->copy()->startOfYear(),
                'end' => $now->copy()->endOfYear(),
            ],
        ];

        $serviceTypes = array_fill_keys(array_keys(Booking::LAYANAN), 0);
        $bookingStats = [];

        foreach ($periods as $key => $period) {
            $counts = Booking::query()
                ->whereBetween('created_at', [$period['start'], $period['end']])
                ->selectRaw('jenis_layanan, COUNT(*) as total')
                ->groupBy('jenis_layanan')
                ->pluck('total', 'jenis_layanan')
                ->all();

            $serviceCounts = array_replace($serviceTypes, $counts);
            $bookingStats[$key] = [
                'label' => $period['label'],
                'total' => array_sum($serviceCounts),
                'services' => $serviceCounts,
            ];
        }

        return view('dashboard.owner', [
            'bookingStats' => $bookingStats,
            'serviceTypes' => Booking::LAYANAN,
        ]);
    }
}
