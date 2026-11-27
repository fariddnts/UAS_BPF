@extends('layout.user-dashboard')

@section('title', 'Pesan Jadwal Servis | Bengkel Syifa')

@section('content')
<div class="customer-dashboard">
    @include('layout.dashboard-navigation')

    <main class="customer-main">
        <header class="customer-heading">
            <div>
                <p class="customer-eyebrow">Bengkel Syifa</p>
                <h1>Pesan jadwal servis</h1>
                <p>Lengkapi data motor dan pilih waktu kunjunganmu.</p>
            </div>
            <a class="customer-button customer-button--outline" href="{{ route('dashboard') }}">
                <span aria-hidden="true">←</span>
                Kembali ke beranda
            </a>
        </header>

        @if(session('success'))
            <div class="customer-notice" role="status">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="customer-notice customer-notice--error" role="alert">
                <strong>Periksa kembali data booking:</strong>
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="customer-booking-grid">
            <section class="customer-card customer-booking-form" aria-labelledby="booking-form-title">
                <h2 id="booking-form-title">Data servis</h2>
                <p class="customer-muted">Pilih jadwal yang sesuai. Layanan bengkel tersedia pukul 08.00–16.00.</p>

                <form action="{{ route('booking.store') }}" method="POST">
                    @csrf
                    <div class="customer-field">
                        <label for="plat_nomor">Plat nomor</label>
                        <input type="text" id="plat_nomor" name="plat_nomor" value="{{ old('plat_nomor') }}"
                               placeholder="Contoh: BH 1234 AB" maxlength="15" required>
                    </div>

                    <div class="customer-field">
                        <label for="jenis_motor">Jenis motor</label>
                        <input type="text" id="jenis_motor" name="jenis_motor" value="{{ old('jenis_motor') }}"
                               placeholder="Contoh: Honda Beat 2020" maxlength="100" required>
                    </div>

                    <div class="customer-field">
                        <label for="jenis_layanan">Jenis layanan</label>
                        <select id="jenis_layanan" name="jenis_layanan" required>
                            <option value="">Pilih layanan</option>
                            @foreach(\App\Models\Booking::LAYANAN as $value => $label)
                                <option value="{{ $value }}" @selected(old('jenis_layanan', $selectedService) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="customer-field">
                        <label for="keluhan">Keluhan <span>(opsional)</span></label>
                        <textarea id="keluhan" name="keluhan" rows="3" maxlength="500">{{ old('keluhan') }}</textarea>
                    </div>

                    <div class="customer-booking-date">
                        <div class="customer-field">
                            <label for="tanggal_booking">Tanggal</label>
                            <input type="date" id="tanggal_booking" name="tanggal_booking"
                                   min="{{ now()->toDateString() }}" value="{{ old('tanggal_booking') }}" required>
                        </div>
                        <div class="customer-field">
                            <label for="jam_booking">Jam</label>
                            <input type="time" id="jam_booking" name="jam_booking" min="08:00" max="16:00"
                                   value="{{ old('jam_booking') }}" required>
                        </div>
                    </div>

                    <p class="customer-booking-help">Estimasi pengerjaan dapat berubah sesuai kondisi motor. Jika jam pilihan penuh, silakan tentukan waktu lain.</p>
                    <button class="customer-button customer-button--primary customer-booking-submit" type="submit">Pesan jadwal</button>
                </form>
            </section>

            <section class="customer-card customer-booking-orders" aria-labelledby="booking-orders-title">
                <div class="customer-booking-orders__heading">
                    <div>
                        <p class="customer-eyebrow">Aktivitas</p>
                        <h2 id="booking-orders-title">Pesanan saya</h2>
                    </div>
                    <span>{{ $bookings->count() }} pesanan</span>
                </div>
                @forelse($bookings as $booking)
                    <article class="customer-booking-order">
                        <div class="customer-booking-order__top">
                            <strong>{{ \App\Models\Booking::LAYANAN[$booking->jenis_layanan] ?? $booking->jenis_layanan }}</strong>
                            <span class="customer-status-pill customer-status-pill--{{ $booking->status }}">
                                <i aria-hidden="true"></i>{{ $booking->statusLabel() }}
                            </span>
                        </div>
                        <p>{{ $booking->jenis_motor }} <span>·</span> {{ $booking->plat_nomor }}</p>
                        <time datetime="{{ $booking->tanggal_booking->toDateString() }}">
                            {{ $booking->tanggal_booking->format('d M Y') }} · {{ substr($booking->jam_booking, 0, 5) }}
                        </time>
                    </article>
                @empty
                    <div class="customer-booking-empty">
                        <span aria-hidden="true">◷</span>
                        <strong>Belum ada pesanan</strong>
                        <p class="customer-muted">Booking pertamamu akan muncul di sini beserta status servisnya.</p>
                    </div>
                @endforelse
            </section>
        </div>
    </main>
</div>
@endsection
