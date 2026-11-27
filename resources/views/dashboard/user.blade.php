@extends('layout.user-dashboard')

@section('title', 'Servis saya | Bengkel Syifa')

@push('scripts')
    <script src="{{ asset('assets/js/moto3d.js') }}"></script>
    <script src="{{ asset('assets/js/dashboard.js') }}"></script>
@endpush

@section('content')
@php
    $currentStep = match ($currentBooking?->status) {
        'pending' => 1,
        'on_progress' => 2,
        'finished' => 3,
        default => 0,
    };
    $statusName = match ($currentBooking?->status) {
        'pending' => 'Menunggu',
        'on_progress' => 'Dikerjakan',
        'finished' => 'Selesai',
        default => null,
    };
    $serviceName = $currentBooking
        ? (\App\Models\Booking::LAYANAN[$currentBooking->jenis_layanan] ?? $currentBooking->jenis_layanan)
        : null;
@endphp

<div class="customer-dashboard">
    <aside class="customer-sidebar">
        <a class="customer-brand" href="{{ route('dashboard') }}" aria-label="Bengkel Syifa, beranda">
            <svg viewBox="0 0 32 32" fill="none" aria-hidden="true">
                <path d="M16 2 28 9v14l-12 7L4 23V9z" stroke="currentColor" stroke-width="2"/>
                <circle cx="16" cy="16" r="5.5" stroke="currentColor" stroke-width="2"/>
                <path d="M1 16h30" stroke="currentColor" stroke-width="2"/>
            </svg>
            <span>Bengkel Syifa</span>
        </a>

        <nav class="customer-nav" aria-label="Menu pelanggan">
            <a class="is-active" href="#service-status" aria-current="page">Servis saya</a>
            <a href="{{ route('booking.create') }}">Booking</a>
            <a href="#service-history">Riwayat</a>
            <a href="#vehicle-details">Kendaraan</a>
        </nav>

        <div class="customer-profile">
            <span class="customer-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
            <span class="customer-profile__name">
                <strong>{{ auth()->user()->name }}</strong>
                <small>Pelanggan</small>
            </span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="customer-logout" type="submit">Keluar</button>
            </form>
        </div>
    </aside>

    <main class="customer-main">
        <header class="customer-heading">
            <div>
                <h1>Servis saya</h1>
                <p>Halo {{ auth()->user()->name }}, pantau jadwal dan progres servis motor Anda.</p>
            </div>
            <a class="customer-button customer-button--outline" href="{{ route('home') }}">
                <span aria-hidden="true">↗</span>
                Lihat animasi motor
            </a>
        </header>

        @if(session('success'))
            <div class="customer-notice" role="status">{{ session('success') }}</div>
        @endif

        <div class="customer-grid">
            <section class="customer-card customer-status" id="service-status" aria-labelledby="service-status-title">
                <header class="customer-status__heading">
                    <div>
                        @if($currentBooking)
                            <h2 id="service-status-title">{{ $currentBooking->plat_nomor }}</h2>
                            <p>{{ $serviceName }}@if($currentBooking->mekanik), mekanik {{ $currentBooking->mekanik->name }}@endif</p>
                        @else
                            <h2 id="service-status-title">Belum ada servis berjalan</h2>
                            <p>Booking servis untuk melihat progres motor Anda di sini.</p>
                        @endif
                    </div>
                    @if($currentBooking)
                        <span class="customer-status-pill customer-status-pill--{{ $currentBooking->status }}">
                            <i aria-hidden="true"></i>{{ $statusName }}
                        </span>
                    @endif
                </header>

                <canvas class="holo big customer-motor" data-part="rem" aria-label="Animasi motor dengan area rem disorot"></canvas>

                @if($currentBooking)
                    <ol class="customer-stepper" aria-label="Tahapan servis">
                        @foreach(['Booking', 'Menunggu', 'Dikerjakan', 'Selesai'] as $index => $step)
                            <li class="{{ $index < $currentStep ? 'is-done' : ($index === $currentStep ? 'is-current' : '') }}">
                                <span class="customer-stepper__marker" aria-hidden="true">{{ $index < $currentStep ? '✓' : '' }}</span>
                                <strong>{{ $step }}</strong>
                                @if($index === 0)
                                    <small>{{ $currentBooking->tanggal_booking->format('d/m') }} · {{ substr($currentBooking->jam_booking, 0, 5) }}</small>
                                @elseif($index === $currentStep && $currentBooking->status === 'pending')
                                    <small>Menunggu konfirmasi</small>
                                @elseif($index === $currentStep && $currentBooking->status === 'on_progress')
                                    <small>Sedang dikerjakan</small>
                                @elseif($index === 3 && $currentBooking->status === 'finished')
                                    <small>Selesai</small>
                                @else
                                    <small>&nbsp;</small>
                                @endif
                            </li>
                        @endforeach
                    </ol>
                @else
                    <div class="customer-empty-action">
                        <a class="customer-button customer-button--primary" href="{{ route('booking.create') }}">Booking servis</a>
                    </div>
                @endif
            </section>

            <div class="customer-side">
                <section class="customer-card customer-estimate" aria-labelledby="estimate-title">
                    <h2 id="estimate-title">Estimasi biaya</h2>
                    @if($currentBooking)
                        @if($currentBooking->status === 'finished')
                            <p class="customer-muted">Informasi pembayaran untuk servis ini belum tersedia.</p>
                        @else
                            <p class="customer-muted">Rincian biaya akan ditampilkan setelah mekanik menyelesaikan pemeriksaan.</p>
                        @endif
                        <div class="customer-estimate__status">
                            @if($currentBooking->status === 'finished')
                                <span class="customer-status-pill customer-status-pill--finished"><i aria-hidden="true"></i>Servis selesai</span>
                            @else
                            <span class="customer-status-pill customer-status-pill--pending"><i aria-hidden="true"></i>Menunggu estimasi</span>
                            @endif
                        </div>
                    @else
                        <p class="customer-muted">Belum ada estimasi biaya. Buat booking untuk memulai servis.</p>
                    @endif
                </section>

                <section class="customer-card customer-animation">
                    <div>
                        <p class="customer-eyebrow">Jelajahi motor</p>
                        <h2>Lihat animasi pemeriksaan</h2>
                        <p class="customer-muted">Masuk ke halaman animasi motor dan lihat cara pemeriksaan dilakukan.</p>
                    </div>
                    <a class="customer-button customer-button--primary" href="{{ route('home') }}#pindai">Buka animasi motor</a>
                </section>

                <section class="customer-card customer-history" id="service-history" aria-labelledby="history-title">
                    <h2 id="history-title">Riwayat servis</h2>
                    @forelse($serviceHistory as $booking)
                        <article class="customer-history__row">
                            <time datetime="{{ $booking->tanggal_booking->toDateString() }}">{{ $booking->tanggal_booking->format('d M Y') }}</time>
                            <span>
                                <strong>{{ \App\Models\Booking::LAYANAN[$booking->jenis_layanan] ?? $booking->jenis_layanan }}</strong>
                                <small>{{ $booking->plat_nomor }}</small>
                            </span>
                            <b>Selesai</b>
                        </article>
                    @empty
                        <p class="customer-muted customer-history__empty">Riwayat servis yang selesai akan muncul di sini.</p>
                    @endforelse
                </section>
            </div>
        </div>

        @if($currentBooking)
            <section class="customer-card customer-vehicle" id="vehicle-details" aria-labelledby="vehicle-title">
                <div>
                    <p class="customer-eyebrow">Kendaraan</p>
                    <h2 id="vehicle-title">{{ $currentBooking->jenis_motor }}</h2>
                    <p>{{ $currentBooking->plat_nomor }} · {{ $serviceName }}</p>
                </div>
                <a class="customer-button customer-button--outline" href="{{ route('booking.create') }}">Booking lagi</a>
            </section>
        @endif
    </main>
</div>
@endsection
