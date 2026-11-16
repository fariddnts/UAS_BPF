@extends('layout.admin.app')

@section('title', 'Ringkasan Servis')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard-owner.css') }}">
@endpush

@section('content')
@php
    $chartValues = [];
    foreach ($bookingStats as $stats) {
        foreach ($stats['services'] as $count) {
            $chartValues[] = $count;
        }
    }
    $chartMax = max(1, ...$chartValues);
    $periodStyles = [
        'daily' => 'daily',
        'monthly' => 'monthly',
        'yearly' => 'yearly',
    ];
@endphp

<div class="owner-dashboard">
    <section class="owner-chart" aria-labelledby="owner-chart-title">
        <div class="owner-chart__heading">
            <div>
                <p class="owner-services__eyebrow">Performa bengkel</p>
                <h1 id="owner-chart-title">Grafik Ringkasan Servis</h1>
                <p>Perbandingan pesanan tiap jenis servis berdasarkan periode.</p>
            </div>
            <div class="owner-chart__legend" aria-label="Keterangan periode">
                <span><i class="owner-chart__dot owner-chart__dot--daily"></i>Hari ini</span>
                <span><i class="owner-chart__dot owner-chart__dot--monthly"></i>Bulan ini</span>
                <span><i class="owner-chart__dot owner-chart__dot--yearly"></i>Tahun ini</span>
            </div>
        </div>

        <div class="owner-chart__plot" role="img" aria-label="Grafik jumlah pesanan servis per jenis untuk hari ini, bulan ini, dan tahun ini">
            <div class="owner-chart__rows">
                @foreach($serviceTypes as $serviceKey => $serviceLabel)
                    <div class="owner-chart__row">
                        <div class="owner-chart__service">{{ $serviceLabel }}</div>
                        <div class="owner-chart__bars">
                            @foreach(['daily', 'monthly', 'yearly'] as $period)
                                @php
                                    $count = $bookingStats[$period]['services'][$serviceKey];
                                    $barWidth = $count > 0 ? max(2, ($count / $chartMax) * 100) : 0;
                                @endphp
                                <div class="owner-chart__bar-track">
                                    <span class="owner-chart__bar owner-chart__bar--{{ $period }}"
                                          style="width: {{ $barWidth }}%"
                                          aria-hidden="true"></span>
                                    <span class="owner-chart__bar-value">{{ number_format($count) }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="owner-hero" aria-labelledby="owner-title">
        <div class="owner-hero__copy">
            <p class="owner-hero__eyebrow">Bengkel Syifa · Panel Owner</p>
            <h1 id="owner-title">Ringkasan Servis</h1>
            <p>Pantau aktivitas pesanan servis dan layanan yang paling banyak dipilih pelanggan.</p>
            <div class="owner-hero__note">
                <span class="owner-hero__note-icon" aria-hidden="true">↗</span>
                <span>Data dirangkum berdasarkan waktu pemesanan.</span>
            </div>
        </div>
        <div class="owner-hero__visual" aria-hidden="true">
            <span class="owner-hero__shape owner-hero__shape--blue"></span>
            <span class="owner-hero__shape owner-hero__shape--orange"></span>
            <img src="{{ asset('assets/images/service-highlights/routine.jpg') }}" alt="">
            <div class="owner-hero__visual-label">
                <span>Total tahun ini</span>
                <strong>{{ number_format($bookingStats['yearly']['total']) }}</strong>
                <span>pesanan servis</span>
            </div>
        </div>
    </section>

    <section class="owner-periods" aria-label="Ringkasan jumlah pesanan">
        @foreach ($bookingStats as $key => $stats)
            <article class="owner-period-card owner-period-card--{{ $periodStyles[$key] ?? 'daily' }}">
                <div class="owner-period-card__top">
                    <span class="owner-period-card__icon" aria-hidden="true">
                        @if($key === 'daily')
                            <svg viewBox="0 0 24 24" fill="none"><path d="M12 7v5l3 2m6-2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        @elseif($key === 'monthly')
                            <svg viewBox="0 0 24 24" fill="none"><rect x="3" y="5" width="18" height="16" rx="2" stroke="currentColor" stroke-width="1.8"/><path d="M16 3v4M8 3v4M3 10h18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                        @else
                            <svg viewBox="0 0 24 24" fill="none"><path d="M4 19V5m0 14h17M8 15l4-4 3 2 6-7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        @endif
                    </span>
                    <span class="owner-period-card__label">{{ $stats['label'] }}</span>
                </div>
                <p class="owner-period-card__value">{{ number_format($stats['total']) }}</p>
                <p class="owner-period-card__caption">pesanan servis</p>
                <div class="owner-period-card__bar" aria-hidden="true"></div>
            </article>
        @endforeach
    </section>

    <section class="owner-services" aria-labelledby="owner-services-title">
        <div class="owner-services__heading">
            <div>
                <p class="owner-services__eyebrow">Analisis layanan</p>
                <h2 id="owner-services-title">Pesanan per Jenis Servis</h2>
                <p>Perbandingan pesanan servis untuk setiap periode.</p>
            </div>
            <span class="owner-services__badge">{{ count($serviceTypes) }} jenis layanan</span>
        </div>

        <div class="card border-0 shadow owner-services__card">
            <div class="table-responsive">
                <table class="table table-centered mb-0 owner-services__table">
                    <thead>
                        <tr>
                            <th>Jenis Servis</th>
                            <th>Hari Ini</th>
                            <th>Bulan Ini</th>
                            <th>Tahun Ini</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($serviceTypes as $serviceKey => $serviceLabel)
                            <tr>
                                <th scope="row">
                                    <span class="owner-services__service-icon" aria-hidden="true">⚙</span>
                                    {{ $serviceLabel }}
                                </th>
                                <td>{{ number_format($bookingStats['daily']['services'][$serviceKey]) }}</td>
                                <td>{{ number_format($bookingStats['monthly']['services'][$serviceKey]) }}</td>
                                <td>{{ number_format($bookingStats['yearly']['services'][$serviceKey]) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>
@endsection
