<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function status(Request $request)
    {
        $bookingQuery = Booking::query()
            ->where('user_id', $request->user()->id)
            ->orderByDesc('tanggal_booking')
            ->orderByDesc('jam_booking');

        $currentBooking = (clone $bookingQuery)
            ->where('status', '!=', 'finished')
            ->first(['id', 'status', 'updated_at'])
            ?? $bookingQuery->first(['id', 'status', 'updated_at']);
        $estimateBookingId = filter_var($request->query('estimate_booking'), FILTER_VALIDATE_INT);
        $estimateBooking = $estimateBookingId
            ? Booking::query()
                ->where('user_id', $request->user()->id)
                ->whereKey($estimateBookingId)
                ->first(['id', 'updated_at'])
            : $currentBooking;

        return response()->json([
            'bookingId' => $currentBooking?->id,
            'status' => $currentBooking?->status,
            'updatedAt' => $currentBooking?->updated_at?->timestamp,
            'estimateBookingId' => $estimateBooking?->id,
            'estimateUpdatedAt' => $estimateBooking?->updated_at?->timestamp,
        ])->header('Cache-Control', 'private, no-store');
    }

    public function index(Request $request)
    {
        $user = auth()->user();

        // User: halaman beranda layanan
        if ($user->hasRole('user')) {
            $bookings = Booking::query()
                ->where('user_id', $user->id)
                ->with('mekanik')
                ->orderByDesc('tanggal_booking')
                ->orderByDesc('jam_booking')
                ->get();

            $currentBooking = $bookings->first(
                fn (Booking $booking): bool => $booking->status !== 'finished'
            ) ?? $bookings->first();
            $estimateBookingId = filter_var($request->query('estimate_booking'), FILTER_VALIDATE_INT);
            $estimateBooking = $estimateBookingId
                ? ($bookings->firstWhere('id', $estimateBookingId) ?? $currentBooking)
                : $currentBooking;
            $serviceHistory = $bookings
                ->filter(fn (Booking $booking): bool => $booking->status === 'finished')
                ->take(5);

            return view('dashboard.user', compact('currentBooking', 'estimateBooking', 'serviceHistory'));
        }

        // Admin dan mekanik: daftar pekerjaan
        if ($user->hasRole('admin', 'mekanik')) {
            $bookingQuery = Booking::with('user');
            if ($user->hasRole('admin')) {
                $bookingQuery->whereNull('payment_verified_at');
            }

            $bookings = $bookingQuery
                ->orderByRaw("FIELD(status, 'pending', 'diagnosed', 'on_progress', 'finished')")
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

    public function completedUsers()
    {
        $bookings = Booking::query()
            ->with('user')
            ->where('status', 'finished')
            ->where('metode_pembayaran', 'qris')
            ->whereNotNull('payment_verified_at')
            ->orderByDesc('payment_verified_at')
            ->get();

        return view('admin.completed-users', compact('bookings'));
    }
}
