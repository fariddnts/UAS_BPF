@extends('layout.admin.app')

@section('title', 'Pesan Jadwal Servis')

@section('content')
<div class="py-4">
    <h1 class="h4">Pesan Jadwal Servis</h1>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
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

<div class="row">
    <div class="col-lg-5 mb-4">
        <div class="card border-0 shadow p-4">
            <form action="{{ route('booking.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label class="form-label" for="plat_nomor">Plat Nomor</label>
                    <input type="text" id="plat_nomor" name="plat_nomor" class="form-control"
                           value="{{ old('plat_nomor') }}" placeholder="Contoh: BH 1234 AB" required>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="jenis_motor">Jenis Motor</label>
                    <input type="text" id="jenis_motor" name="jenis_motor" class="form-control"
                           value="{{ old('jenis_motor') }}" placeholder="Contoh: Honda Beat 2020" required>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="jenis_layanan">Jenis Layanan</label>
                    <select id="jenis_layanan" name="jenis_layanan" class="form-select" required>
                        <option value="">Pilih layanan</option>
                        @foreach(\App\Models\Booking::LAYANAN as $value => $label)
                            <option value="{{ $value }}" @selected(old('jenis_layanan') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="keluhan">Keluhan (opsional)</label>
                    <textarea id="keluhan" name="keluhan" rows="3" class="form-control">{{ old('keluhan') }}</textarea>
                </div>

                <div class="row">
                    <div class="col-7 mb-3">
                        <label class="form-label" for="tanggal_booking">Tanggal</label>
                        <input type="date" id="tanggal_booking" name="tanggal_booking" class="form-control"
                               min="{{ now()->toDateString() }}" value="{{ old('tanggal_booking') }}" required>
                    </div>
                    <div class="col-5 mb-3">
                        <label class="form-label" for="jam_booking">Jam</label>
                        <input type="time" id="jam_booking" name="jam_booking" class="form-control"
                               min="08:00" max="16:00" value="{{ old('jam_booking') }}" required>
                    </div>
                </div>
                <p class="small text-muted">Jam layanan 08:00 sampai 16:00. Estimasi pengerjaan bisa berubah sesuai kondisi motor.</p>

                <button type="submit" class="btn btn-primary w-100">Pesan Jadwal</button>
            </form>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card border-0 shadow">
            <div class="card-header"><h2 class="h5 mb-0">Pesanan Saya</h2></div>
            <div class="table-responsive">
                <table class="table table-centered mb-0">
                    <thead>
                        <tr>
                            <th>Jadwal</th>
                            <th>Motor</th>
                            <th>Layanan</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bookings as $b)
                            <tr>
                                <td>{{ $b->tanggal_booking->format('d M Y') }}<br>
                                    <small>{{ substr($b->jam_booking, 0, 5) }}</small></td>
                                <td>{{ $b->jenis_motor }}<br><small>{{ $b->plat_nomor }}</small></td>
                                <td>{{ \App\Models\Booking::LAYANAN[$b->jenis_layanan] ?? $b->jenis_layanan }}</td>
                                <td><span class="badge {{ $b->badgeClass() }}">{{ $b->statusLabel() }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center py-4">Belum ada pesanan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
