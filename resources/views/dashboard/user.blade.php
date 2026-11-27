@extends('layout.user-dashboard')

@section('title', 'Servis saya | Bengkel Syifa')

@push('scripts')
    <script src="{{ asset('assets/js/moto3d.js') }}"></script>
    <script src="{{ asset('assets/js/dashboard.js') }}?v=5"></script>
@endpush

@section('content')
@php
    $processStep = match ($currentBooking?->status) {
        'pending' => 1,
        'diagnosed' => 2,
        'on_progress' => 3,
        'finished' => 4,
        default => 0,
    };
    $processProgress = $processStep * 25;
    $statusName = match ($currentBooking?->status) {
        'pending' => 'Booking',
        'diagnosed' => 'Diagnosa',
        'on_progress' => 'Pengerjaan',
        'finished' => $currentBooking?->metode_pembayaran === 'qris' && ! $currentBooking?->payment_verified_at
            ? 'Belum siap'
            : 'Servis selesai',
        default => null,
    };
    $statusClass = $currentBooking?->status === 'finished'
        && $currentBooking?->metode_pembayaran === 'qris'
        && ! $currentBooking?->payment_verified_at
            ? 'pending'
            : $currentBooking?->status;
    $serviceName = $currentBooking
        ? (\App\Models\Booking::LAYANAN[$currentBooking->jenis_layanan] ?? $currentBooking->jenis_layanan)
        : null;
@endphp

<div class="customer-dashboard"
     data-status-url="{{ route('dashboard.status', $estimateBooking ? ['estimate_booking' => $estimateBooking->id] : [], false) }}"
     data-current-booking-id="{{ $currentBooking?->id ?? '' }}"
     data-current-booking-status="{{ $currentBooking?->status ?? '' }}"
     data-current-booking-updated-at="{{ $currentBooking?->updated_at?->timestamp ?? '' }}"
     data-estimate-booking-id="{{ $estimateBooking?->id ?? '' }}"
     data-estimate-booking-updated-at="{{ $estimateBooking?->updated_at?->timestamp ?? '' }}">
    @include('layout.dashboard-navigation')

    <main class="customer-main">
        <header class="customer-heading">
            <div>
                <h1>Servis saya</h1>
                <p>Halo {{ auth()->user()->name }}, pantau jadwal dan progres servis motor Anda.</p>
            </div>
            <a class="customer-button customer-button--outline" href="{{ route('home') }}">
                <span aria-hidden="true">←</span>
                Kembali
            </a>
        </header>

        @if(session('success'))
            <div class="customer-notice" role="status">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="customer-notice" role="alert">{{ $errors->first() }}</div>
        @endif

        <section class="customer-process" aria-labelledby="process-title">
            <header class="customer-process__heading">
                <div>
                    <p class="customer-eyebrow">Proses servis</p>
                    <h2 id="process-title">Dari booking sampai kunci kembali</h2>
                </div>
                @if($currentBooking)
                    <span class="customer-status-pill customer-status-pill--{{ $statusClass }}">
                        <i aria-hidden="true"></i>{{ $statusName }}
                    </span>
                @endif
            </header>

            <div class="customer-process__track" aria-hidden="true">
                <span style="width: {{ $processProgress }}%"></span>
            </div>

            <div class="customer-process__grid">
                @foreach(['Booking', 'Diagnosa', 'Pengerjaan', 'Serah terima'] as $index => $step)
                    @php
                        $stepNumber = $index + 1;
                        $stepState = $stepNumber < $processStep ? 'is-done' : ($stepNumber === $processStep ? 'is-current' : '');
                    @endphp
                    <article class="customer-process__card {{ $stepState }}">
                        <span class="customer-process__number" aria-hidden="true">
                            @if($stepNumber < $processStep)
                                ✓
                            @else
                                {{ $stepNumber }}
                            @endif
                        </span>
                        <h3>{{ $step }}</h3>

                        @if($stepNumber === 1)
                            @if($currentBooking)
                                <p class="customer-process__description">Jadwal servis yang Anda pesan.</p>
                                <dl class="customer-process__details">
                                    <div><dt>Waktu</dt><dd>{{ $currentBooking->tanggal_booking->format('d M Y') }} · {{ substr($currentBooking->jam_booking, 0, 5) }}</dd></div>
                                    <div><dt>Motor</dt><dd>{{ $currentBooking->jenis_motor }}</dd></div>
                                    <div><dt>Plat</dt><dd>{{ $currentBooking->plat_nomor }}</dd></div>
                                    <div><dt>Layanan</dt><dd>{{ $serviceName }}</dd></div>
                                </dl>
                                @if($currentBooking->keluhan)
                                    <p class="customer-process__note">Keluhan: {{ $currentBooking->keluhan }}</p>
                                @endif
                            @else
                                <p class="customer-process__description">Belum ada jadwal servis aktif.</p>
                                <a class="customer-button customer-button--primary" href="{{ route('booking.create', [], false) }}">Booking servis</a>
                            @endif
                        @elseif($stepNumber === 2)
                            <p class="customer-process__description">Pemeriksaan mencakup mesin, rem, kaki-kaki, dan kelistrikan.</p>
                            <p class="customer-process__note">
                                @if(!$currentBooking)
                                    Buat booking untuk memulai proses pemeriksaan.
                                @elseif($currentBooking->status === 'pending')
                                    Pemeriksaan menunggu motor masuk ke bengkel.
                                @elseif($currentBooking->status === 'diagnosed')
                                    Diagnosa sedang dilakukan; hasil terperinci belum tersedia di sistem.
                                @else
                                    Hasil diagnosa terperinci belum tersedia di sistem.
                                @endif
                            </p>
                        @elseif($stepNumber === 3)
                            <p class="customer-process__description">
                                @if(!$currentBooking)
                                    Status pengerjaan akan muncul setelah booking dibuat.
                                @elseif($currentBooking->status === 'pending')
                                    Menunggu mekanik memulai diagnosa.
                                @elseif($currentBooking->status === 'diagnosed')
                                    Menunggu diagnosa selesai sebelum pengerjaan dimulai.
                                @elseif($currentBooking->status === 'on_progress')
                                    Servis sedang dikerjakan{{ $currentBooking->mekanik ? ' oleh '.$currentBooking->mekanik->name : '' }}.
                                @else
                                    Pengerjaan servis telah selesai.
                                @endif
                            </p>
                        @else
                            <p class="customer-process__description">
                                @if($currentBooking && $currentBooking->status === 'finished')
                                    @if($currentBooking->metode_pembayaran === 'qris' && ! $currentBooking->payment_verified_at)
                                        @if($currentBooking->payment_proof_path)
                                            Bukti QRIS menunggu verifikasi admin. Status servis: Belum siap.
                                        @else
                                            Unggah bukti pembayaran QRIS agar admin dapat memverifikasinya. Status servis: Belum siap.
                                        @endif
                                    @elseif($currentBooking->total_biaya !== null)
                                        Servis selesai. Rincian biaya tersedia di panel estimasi.
                                    @else
                                        Servis selesai. Mekanik belum mencatat total biaya.
                                    @endif
                                @else
                                    Informasi tagihan dan serah terima akan tersedia setelah servis selesai.
                                @endif
                            </p>
                        @endif
                    </article>
                @endforeach
            </div>
        </section>

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
                        <span class="customer-status-pill customer-status-pill--{{ $statusClass }}">
                            <i aria-hidden="true"></i>{{ $statusName }}
                        </span>
                    @endif
                </header>

                <canvas class="holo big customer-motor" data-part="rem" aria-label="Animasi motor dengan area rem disorot"></canvas>

                @if(!$currentBooking)
                    <div class="customer-empty-action">
                        <a class="customer-button customer-button--primary" href="{{ route('booking.create', [], false) }}">Booking servis</a>
                    </div>
                @endif
            </section>

            <div class="customer-side">
                <section class="customer-card customer-estimate" aria-labelledby="estimate-title">
                    <h2 id="estimate-title">Estimasi biaya</h2>
                    @if($estimateBooking)
                        @if($estimateBooking->status === 'finished' && $estimateBooking->total_biaya !== null)
                            <p class="customer-muted">
                                {{ $estimateBooking->plat_nomor }} ·
                                {{ $estimateBooking->tanggal_booking->format('d M Y') }}
                            </p>
                            <p class="customer-estimate__label">Total harga servis</p>
                            <p class="customer-estimate__total">Rp{{ number_format((int) $estimateBooking->total_biaya, 0, ',', '.') }}</p>
                            @if(! $estimateBooking->payment_verified_at)
                                <form action="{{ route('booking.payment', $estimateBooking, false) }}" method="POST" class="customer-payment-form">
                                    @csrf
                                    <fieldset>
                                        <legend>Metode pembayaran</legend>
                                        <label>
                                            <input type="radio" name="metode_pembayaran" value="cash"
                                                   @checked($estimateBooking->metode_pembayaran === 'cash') required>
                                            <span>Cash</span>
                                        </label>
                                        <label>
                                            <input type="radio" name="metode_pembayaran" value="qris"
                                                   @checked($estimateBooking->metode_pembayaran === 'qris') required>
                                            <span>QRIS</span>
                                        </label>
                                    </fieldset>
                                </form>
                            @endif
                            @if($estimateBooking->metode_pembayaran === 'cash')
                                <p class="customer-payment-message" role="status">Silakan lakukan pembayaran ke kasir bengkel.</p>
                            @elseif($estimateBooking->metode_pembayaran === 'qris')
                                <p class="customer-payment-message">
                                    {{ $estimateBooking->payment_verified_at ? 'Pembayaran QRIS telah diverifikasi.' : 'Pilih QRIS untuk membuka halaman pembayaran dan mengunggah bukti transfer.' }}
                                </p>
                                <a class="customer-button customer-button--primary customer-qris-link"
                                   href="{{ route('booking.payment.qris', $estimateBooking, false) }}">
                                    {{ $estimateBooking->payment_verified_at ? 'Lihat status pembayaran' : 'Buka Halaman Pembayaran QRIS' }}
                                </a>
                            @endif
                        @elseif($estimateBooking->status === 'finished')
                            <p class="customer-muted">Servis selesai, tetapi total biaya belum dicatat mekanik.</p>
                        @else
                            <p class="customer-muted">Total biaya dan pilihan pembayaran tersedia setelah servis selesai.</p>
                        @endif
                        <div class="customer-estimate__status">
                            @if($estimateBooking->payment_verified_at || ($estimateBooking->metode_pembayaran !== 'qris' && $estimateBooking->status === 'finished' && $estimateBooking->total_biaya !== null))
                                <span class="customer-status-pill customer-status-pill--finished"><i aria-hidden="true"></i>Servis selesai</span>
                            @elseif($estimateBooking->metode_pembayaran === 'qris' && $estimateBooking->status === 'finished')
                                <span class="customer-status-pill customer-status-pill--pending"><i aria-hidden="true"></i>Belum siap</span>
                            @else
                                <span class="customer-status-pill customer-status-pill--pending"><i aria-hidden="true"></i>Menunggu estimasi</span>
                            @endif
                        </div>
                    @else
                        <p class="customer-muted">Belum ada estimasi biaya. Buat booking untuk memulai servis.</p>
                    @endif
                </section>

                <section class="customer-card customer-history" id="service-history" aria-labelledby="history-title">
                    <h2 id="history-title">Riwayat servis</h2>
                    @forelse($serviceHistory as $booking)
                        <article class="customer-history__row">
                            <time datetime="{{ $booking->tanggal_booking->toDateString() }}">{{ $booking->tanggal_booking->format('d M Y') }}</time>
                            <span>
                                <strong>{{ \App\Models\Booking::LAYANAN[$booking->jenis_layanan] ?? $booking->jenis_layanan }}</strong>
                                <small>{{ $booking->plat_nomor }} ·
                                    {{ $booking->total_biaya !== null ? 'Rp'.number_format((int) $booking->total_biaya, 0, ',', '.') : 'Biaya belum dicatat' }}
                                </small>
                                <small>
                                    Metode:
                                    @if($booking->metode_pembayaran === 'qris')
                                        QRIS · {{ $booking->payment_verified_at ? 'terverifikasi' : ($booking->payment_proof_path ? 'menunggu verifikasi' : 'bukti belum diunggah') }}
                                    @elseif($booking->metode_pembayaran === 'cash')
                                        Cash · ke kasir
                                    @else
                                        Belum dipilih
                                    @endif
                                </small>
                            </span>
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
                <a class="customer-button customer-button--outline" href="{{ route('booking.create', [], false) }}">Booking lagi</a>
            </section>
        @endif
    </main>
</div>
@endsection
