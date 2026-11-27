<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class BookingController extends Controller
{
    public function create(Request $request)
    {
        $selectedService = $request->query('jenis_layanan');

        if (! is_string($selectedService) || ! array_key_exists($selectedService, Booking::LAYANAN)) {
            $selectedService = '';
        }

        $bookings = Booking::where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return view('dashboard.booking', compact('bookings', 'selectedService'));
    }

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
        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(Booking::STATUSES))],
            'total_biaya' => ['required_if:status,finished', 'nullable', 'integer', 'min:0', 'max:999999999999'],
        ]);

        $updates = [
            'status'     => $request->status,
            'mekanik_id' => $request->user()->id,
        ];

        if (isset($data['total_biaya'])) {
            $updates['total_biaya'] = $data['total_biaya'];
        }

        if ($data['status'] !== 'finished') {
            $updates['metode_pembayaran'] = null;
            $updates['payment_proof_path'] = null;
            $updates['payment_verified_at'] = null;
        }

        $previousProof = null;
        if (array_key_exists('payment_proof_path', $updates)) {
            $previousProof = $booking->payment_proof_path;
        } elseif (isset($updates['total_biaya']) && (int) $updates['total_biaya'] !== (int) $booking->total_biaya) {
            $previousProof = $booking->payment_proof_path;
            $updates['payment_proof_path'] = null;
            $updates['payment_verified_at'] = null;
        }

        $booking->update($updates);
        $this->deletePaymentProof($previousProof);

        return back()->with('success', 'Status pengerjaan diperbarui.');
    }

    public function updatePaymentMethod(Request $request, Booking $booking)
    {
        abort_unless((int) $booking->user_id === (int) $request->user()->id, 404);
        abort_unless($booking->status === 'finished' && $booking->total_biaya !== null, 404);

        $data = $request->validate([
            'metode_pembayaran' => ['required', Rule::in(['cash', 'qris'])],
        ]);

        abort_unless($booking->payment_verified_at === null || $data['metode_pembayaran'] === $booking->metode_pembayaran, 422);

        $previousProof = null;
        if ($data['metode_pembayaran'] !== 'qris') {
            $previousProof = $booking->payment_proof_path;
            $data['payment_proof_path'] = null;
            $data['payment_verified_at'] = null;
        }

        $booking->update($data);
        $this->deletePaymentProof($previousProof);

        return redirect()->to($this->estimateUrl($booking))->with('success', 'Metode pembayaran diperbarui.');
    }

    public function showQrisPayment(Request $request, Booking $booking)
    {
        abort_unless(
            (int) $booking->user_id === (int) $request->user()->id
                && $booking->status === 'finished'
                && $booking->total_biaya !== null
                && $booking->metode_pembayaran === 'qris',
            404
        );

        return view('dashboard.payment-qris', compact('booking'));
    }

    public function uploadPaymentProof(Request $request, Booking $booking)
    {
        abort_unless((int) $booking->user_id === (int) $request->user()->id, 404);
        abort_unless(
            $booking->status === 'finished'
                && $booking->total_biaya !== null
                && $booking->metode_pembayaran === 'qris'
                && $booking->payment_verified_at === null,
            404
        );

        $data = $request->validate([
            'bukti_tf' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $previousProof = $booking->payment_proof_path;
        $proofPath = $data['bukti_tf']->store('payment-proofs', 'local');
        if (! is_string($proofPath) || $proofPath === '') {
            return back()->withErrors(['bukti_tf' => 'Bukti transfer gagal disimpan. Silakan coba kembali.']);
        }

        $booking->update([
            'payment_proof_path' => $proofPath,
            'payment_verified_at' => null,
        ]);
        $this->deletePaymentProof($previousProof);

        return redirect()->to(route('booking.payment.qris', $booking, false))
            ->with('success', 'Bukti transfer berhasil dikirim untuk verifikasi.');
    }

    public function updatePaymentVerification(Request $request, Booking $booking)
    {
        abort_unless(
            $booking->status === 'finished'
                && $booking->metode_pembayaran === 'qris'
                && $booking->payment_proof_path,
            404
        );

        if (! Storage::disk('local')->exists($booking->payment_proof_path)) {
            return back()->withErrors(['payment_proof' => 'Bukti transfer tidak ditemukan. Minta user mengunggah ulang bukti.']);
        }

        $verifiedAt = $request->boolean('verified') ? now() : null;
        $booking->update(['payment_verified_at' => $verifiedAt]);

        return back()->with(
            'success',
            $verifiedAt ? 'Pembayaran QRIS berhasil diverifikasi.' : 'Verifikasi pembayaran dibatalkan.'
        );
    }

    public function showPaymentProof(Booking $booking)
    {
        abort_unless($booking->metode_pembayaran === 'qris' && $booking->payment_proof_path, 404);

        $disk = Storage::disk('local');
        abort_unless($disk->exists($booking->payment_proof_path), 404);

        return response()->file($disk->path($booking->payment_proof_path), [
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function estimateUrl(Booking $booking): string
    {
        return route('user.dashboard', ['estimate_booking' => $booking->id], false).'#estimate-title';
    }

    private function deletePaymentProof(?string $path): void
    {
        if (! $path) {
            return;
        }

        $disk = Storage::disk('local');
        if ($disk->exists($path) && ! $disk->delete($path)) {
            Log::warning('Failed to remove replaced payment proof.', ['path' => $path]);
        }
    }
}
