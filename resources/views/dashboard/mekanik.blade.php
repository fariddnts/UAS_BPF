@extends('layout.admin.app')

@section('title', 'Daftar Pekerjaan')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard-mekanik.css') }}">
@endpush

@section('content')
@php
    $pendingCount = $bookings->where('status', 'pending')->count();
    $inProgressCount = $bookings->where('status', 'on_progress')->count();
    $finishedCount = $bookings->where('status', 'finished')->count();
    $isMechanic = auth()->user()->hasRole('mekanik');
@endphp

<div class="mechanic-dashboard">
    <section class="mechanic-hero" aria-labelledby="mechanic-title">
        <div class="mechanic-hero__copy">
            <p class="mechanic-hero__eyebrow">Bengkel Syifa · {{ $isMechanic ? 'Area Kerja' : 'Panel Admin' }}</p>
            <h1 id="mechanic-title">{{ $isMechanic ? 'Pekerjaan Bengkel' : 'Manajemen Bengkel' }}</h1>
            <p>{{ $isMechanic ? 'Pantau jadwal servis pelanggan dan perbarui status pengerjaan motor.' : 'Pantau pesanan servis dan aktivitas pekerjaan bengkel dalam satu tampilan.' }}</p>
            <div class="mechanic-hero__note">
                <span class="mechanic-hero__note-icon" aria-hidden="true">✓</span>
                <span>{{ $isMechanic ? 'Kerjakan setiap servis dengan aman dan teliti.' : 'Ringkasan pekerjaan bengkel diperbarui dari pesanan pelanggan.' }}</span>
            </div>
        </div>
        <div class="mechanic-hero__visual" aria-hidden="true">
            <span class="mechanic-hero__shape"></span>
            <img src="{{ asset('assets/images/service-highlights/routine.jpg') }}" alt="">
            <span class="mechanic-hero__visual-label">Siap melayani</span>
        </div>
    </section>

@if(session('success'))
    <div class="alert alert-success" role="status">{{ session('success') }}</div>
@endif

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

    <section class="mechanic-stats" aria-label="Ringkasan status pekerjaan">
        <article class="mechanic-stat-card mechanic-stat-card--pending">
            <span class="mechanic-stat-card__icon" aria-hidden="true">◷</span>
            <div>
                <p>Menunggu</p>
                <strong>{{ $pendingCount }}</strong>
                <span>pekerjaan</span>
            </div>
        </article>
        <article class="mechanic-stat-card mechanic-stat-card--progress">
            <span class="mechanic-stat-card__icon" aria-hidden="true">↻</span>
            <div>
                <p>Sedang Dikerjakan</p>
                <strong>{{ $inProgressCount }}</strong>
                <span>pekerjaan</span>
            </div>
        </article>
        <article class="mechanic-stat-card mechanic-stat-card--finished">
            <span class="mechanic-stat-card__icon" aria-hidden="true">✓</span>
            <div>
                <p>Selesai</p>
                <strong>{{ $finishedCount }}</strong>
                <span>pekerjaan</span>
            </div>
        </article>
    </section>

    <section class="mechanic-work-section" aria-labelledby="work-list-title">
        <div class="mechanic-work-section__heading">
            <div>
                <p class="mechanic-work-section__eyebrow">Jadwal servis</p>
                <h2 id="work-list-title">{{ $isMechanic ? 'Daftar Pekerjaan' : 'Pesanan Servis' }}</h2>
            </div>
            <span class="mechanic-work-section__count">{{ $bookings->count() }} pesanan</span>
        </div>

        <div class="card border-0 shadow mechanic-work-card">
            <div class="table-responsive">
                <table class="table table-centered mb-0 mechanic-work-table">
                    <thead>
                        <tr>
                            <th>Jadwal</th>
                            <th>Pelanggan</th>
                            <th>Motor</th>
                            <th>Layanan</th>
                            <th>Keluhan</th>
                            <th>Status</th>
                            @if(auth()->user()->hasRole('mekanik'))
                                <th>Ubah Status</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bookings as $b)
                            <tr>
                                <td class="mechanic-work-table__schedule">
                                    <strong>{{ $b->tanggal_booking->format('d M Y') }}</strong>
                                    <small>{{ substr($b->jam_booking, 0, 5) }}</small>
                                </td>
                                <td>{{ $b->user->name }}</td>
                                <td>
                                    <strong>{{ $b->jenis_motor }}</strong>
                                    <small class="mechanic-work-table__subtext">{{ $b->plat_nomor }}</small>
                                </td>
                                <td>
                                    <span class="mechanic-service-tag">{{ \App\Models\Booking::LAYANAN[$b->jenis_layanan] ?? $b->jenis_layanan }}</span>
                                </td>
                                <td class="mechanic-work-table__complaint">{{ $b->keluhan ?: '-' }}</td>
                                <td><span class="badge {{ $b->badgeClass() }}">{{ $b->statusLabel() }}</span></td>
                                @if(auth()->user()->hasRole('mekanik'))
                                    <td>
                                        <form action="{{ route('pekerjaan.status', $b) }}" method="POST" class="mechanic-status-form">
                                            @csrf
                                            @method('PATCH')
                                            <select name="status" class="form-select form-select-sm" aria-label="Ubah status pesanan {{ $b->jenis_motor }}">
                                                @foreach(\App\Models\Booking::STATUSES as $value => $label)
                                                    <option value="{{ $value }}" @selected($b->status === $value)>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                            <button type="submit" class="btn btn-sm btn-primary">Simpan</button>
                                        </form>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ auth()->user()->hasRole('mekanik') ? 7 : 6 }}" class="mechanic-work-table__empty">
                                    <span class="mechanic-work-table__empty-icon" aria-hidden="true">✓</span>
                                    <strong>Belum ada pekerjaan</strong>
                                    <span>Jadwal servis baru akan muncul di sini.</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>
@endsection
