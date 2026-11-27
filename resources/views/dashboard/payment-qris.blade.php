@extends('layout.user-dashboard')

@section('title', 'Pembayaran QRIS | Bengkel Syifa')

@push('scripts')
    <script src="{{ asset('assets/js/payment-status.js') }}"></script>
@endpush

@section('content')
<div class="customer-dashboard customer-qris-page"
     data-payment-status-url="{{ route('dashboard.status', ['estimate_booking' => $booking->id], false) }}"
     data-booking-id="{{ $booking->id }}"
     data-booking-updated-at="{{ $booking->updated_at?->timestamp ?? '' }}">
    <main class="customer-main customer-qris-main">
        @if(session('success'))
            <div class="customer-notice" role="status">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="customer-notice" role="alert">{{ $errors->first() }}</div>
        @endif

        <section class="customer-qris-card" aria-label="Pembayaran QRIS">
            <div class="customer-qris">
                <p>Scan QRIS berikut untuk membayar. Pastikan nama penerima sesuai sebelum konfirmasi.</p>
                <img src="{{ asset('assets/images/qris-bengkel-syifa.jpeg') }}" alt="QRIS pembayaran Bengkel Syifa">
            </div>

            @if(! $booking->payment_verified_at)
                @if($booking->payment_proof_path)
                    <p class="customer-payment-message" role="status">Bukti transfer diterima; menunggu verifikasi admin.</p>
                @endif
                <form action="{{ route('booking.payment-proof', $booking, false) }}" method="POST" enctype="multipart/form-data" class="customer-proof-form">
                    @csrf
                    <label for="payment-proof">Bukti transfer</label>
                    <input id="payment-proof" type="file" name="bukti_tf" accept="image/jpeg,image/png,image/webp" required>
                    <small>Format JPG, PNG, atau WEBP, maksimal 5 MB.</small>
                    <button class="customer-button customer-button--primary" type="submit">
                        {{ $booking->payment_proof_path ? 'Kirim ulang bukti' : 'Unggah bukti transfer' }}
                    </button>
                </form>
            @endif

            <div class="customer-estimate__status customer-qris-status" role="status" aria-live="polite">
                @if($booking->payment_verified_at)
                    <span class="customer-status-pill customer-status-pill--finished"><i aria-hidden="true"></i>Servis selesai</span>
                @else
                    <span class="customer-status-pill customer-status-pill--pending"><i aria-hidden="true"></i>Belum siap</span>
                @endif
            </div>

            <a class="customer-qris-back" href="{{ route('user.dashboard', ['estimate_booking' => $booking->id], false) }}#estimate-title">
                Kembali ke estimasi biaya
            </a>
        </section>
    </main>
</div>
@endsection
