@extends('layout.admin.app')

@section('title', 'Beranda')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard-user.css') }}">
@endpush

@section('content')
<div class="user-dashboard">
    @if(session('success'))
        <div class="alert alert-success" role="status">{{ session('success') }}</div>
    @endif

    @php
        $heroPhotos = [
            ['image' => 'service-highlights/routine.jpg', 'alt' => 'Mekanik memeriksa kendaraan di bengkel'],
            ['image' => 'service-highlights/sparepart.jpg', 'alt' => 'Motor yang siap menjalani perawatan'],
            ['image' => 'service-highlights/oil.jpg', 'alt' => 'Pemeriksaan mesin kendaraan'],
            ['image' => 'service-highlights/other.jpg', 'alt' => 'Detail komponen kendaraan'],
        ];
        $serviceHighlights = [
            [
                'eyebrow' => 'Perawatan mesin',
                'title' => 'Ganti Oli',
                'description' => 'Jaga performa mesin dengan penggantian oli berkala.',
                'service' => 'ganti_oli',
                'image' => 'service-highlights/oil.jpg',
                'alt' => 'Pemeriksaan mesin kendaraan di bengkel',
            ],
            [
                'eyebrow' => 'Komponen motor',
                'title' => 'Ban & Sparepart',
                'description' => 'Periksa kondisi ban dan komponen motor lainnya.',
                'service' => 'ganti_sparepart',
                'image' => 'service-highlights/sparepart.jpg',
                'alt' => 'Sepeda motor yang siap menjalani perawatan',
            ],
            [
                'eyebrow' => 'Cek menyeluruh',
                'title' => 'Servis Rutin',
                'description' => 'Bantu motor tetap nyaman untuk aktivitas harian.',
                'service' => 'servis_rutin',
                'image' => 'service-highlights/routine.jpg',
                'alt' => 'Mekanik sedang memeriksa kendaraan',
            ],
            [
                'eyebrow' => 'Penggantian komponen',
                'title' => 'Aki & Lainnya',
                'description' => 'Sampaikan kebutuhan penggantian komponen motor.',
                'service' => 'lainnya',
                'image' => 'service-highlights/other.jpg',
                'alt' => 'Detail sepeda motor',
            ],
        ];
    @endphp

    <section class="user-hero" aria-labelledby="user-hero-title">
        <div class="user-hero__copy">
            <p class="user-hero__eyebrow">Bengkel Syifa · Perawatan Motor</p>
            <h1 id="user-hero-title">Servis Motor Lebih Nyaman Bersama Bengkel Syifa</h1>
            <p class="user-hero__tagline">PERAWATAN TEPERCAYA, JADWAL LEBIH MUDAH</p>

            <div class="user-hero__callout">
                <h2>Motor kamu butuh perawatan?</h2>
                <p>Pilih layanan yang sesuai dan jadwalkan kunjunganmu dengan mudah.</p>
                <a class="user-hero__button" href="{{ route('booking.create') }}">Pesan Jadwal Sekarang</a>
            </div>
        </div>

        <div class="user-hero__visual" aria-label="Suasana perawatan kendaraan di bengkel">
            <span class="user-hero__shape user-hero__shape--blue" aria-hidden="true"></span>
            <span class="user-hero__shape user-hero__shape--orange" aria-hidden="true"></span>
            @foreach($heroPhotos as $index => $photo)
                <img
                    class="user-hero__photo user-hero__photo--{{ $index + 1 }}"
                    src="{{ asset('assets/images/' . $photo['image']) }}"
                    alt="{{ $photo['alt'] }}"
                    @if($index > 0) loading="lazy" @endif
                >
            @endforeach
        </div>
    </section>

    <section class="service-showcase" aria-labelledby="service-showcase-title">
        <div class="service-showcase__heading">
            <p class="service-showcase__eyebrow">Bengkel Syifa</p>
            <h1 id="service-showcase-title">Pilih Servis yang Tepat untuk Motormu</h1>
            <p>Perawatan rutin atau ada keluhan? Pilih layanan, lalu tentukan jadwal yang nyaman.</p>
        </div>

        <div class="service-carousel" aria-label="Pilihan layanan servis">
            <button class="service-carousel__arrow service-carousel__arrow--previous" type="button"
                    aria-label="Lihat layanan sebelumnya" data-scroll-direction="-1">
                <span aria-hidden="true">&larr;</span>
            </button>
            <div class="service-carousel__track" id="service-carousel-track" tabindex="0">
                @foreach($serviceHighlights as $service)
                    <article class="service-card">
                        <div class="service-card__visual">
                            <img
                                src="{{ asset('assets/images/' . $service['image']) }}"
                                alt="{{ $service['alt'] }}"
                                loading="lazy"
                            >
                            <div class="service-card__overlay">
                                <span class="service-card__eyebrow">{{ $service['eyebrow'] }}</span>
                                <h2>{{ $service['title'] }}</h2>
                                <span class="service-card__price-note">Tanyakan estimasi saat booking</span>
                                <a class="service-card__button" href="{{ route('booking.create', ['jenis_layanan' => $service['service']]) }}">
                                    Pesan Sekarang
                                </a>
                            </div>
                        </div>
                        <p class="service-card__description">{{ $service['description'] }}</p>
                    </article>
                @endforeach
            </div>
            <button class="service-carousel__arrow service-carousel__arrow--next" type="button"
                    aria-label="Lihat layanan berikutnya" data-scroll-direction="1">
                <span aria-hidden="true">&rarr;</span>
            </button>
        </div>
    </section>

    <section class="workshop-contact" aria-labelledby="workshop-contact-title">
        <div class="workshop-contact__heading">
            <p class="service-showcase__eyebrow">Kunjungi atau hubungi kami</p>
            <h2 id="workshop-contact-title">Bengkel Syifa siap membantu</h2>
        </div>
        <div class="workshop-contact__details">
            <article class="workshop-contact__card">
                <span class="workshop-contact__icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path d="M20 10c0 5-8 12-8 12S4 15 4 10a8 8 0 1 1 16 0Z" stroke="currentColor" stroke-width="1.8"/>
                        <circle cx="12" cy="10" r="2.5" stroke="currentColor" stroke-width="1.8"/>
                    </svg>
                </span>
                <div>
                    <h3>Lokasi Bengkel</h3>
                    <address>JL. Mawar, Kuansing, Riau, Indonesia</address>
                </div>
            </article>
            <article class="workshop-contact__card">
                <span class="workshop-contact__icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path d="M7 3h3l2 5-2.5 1.8a15 15 0 0 0 4.7 4.7L16 12l5 2v3c0 2.2-1.8 4-4 4C9.3 21 3 14.7 3 7c0-2.2 1.8-4 4-4Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
                <div>
                    <h3>Nomor Kontak</h3>
                    <a href="tel:081314319622">0813-1431-9622</a>
                </div>
            </article>
        </div>
    </section>

</div>
@endsection

@push('scripts')
    <script>
        document.querySelectorAll('.service-carousel__arrow').forEach((arrow) => {
            arrow.addEventListener('click', () => {
                const track = document.getElementById('service-carousel-track');
                const direction = Number(arrow.dataset.scrollDirection);
                track.scrollBy({ left: direction * track.clientWidth * 0.8, behavior: 'smooth' });
            });
        });
    </script>
@endpush
