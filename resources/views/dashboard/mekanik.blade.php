@extends('layout.admin.app')

@section('title', 'Daftar Pekerjaan')

@section('content')
<div class="py-4">
    <h1 class="h4">Daftar Pekerjaan</h1>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card border-0 shadow">
    <div class="table-responsive">
        <table class="table table-centered mb-0">
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
                        <td>{{ $b->tanggal_booking->format('d M Y') }}<br>
                            <small>{{ substr($b->jam_booking, 0, 5) }}</small></td>
                        <td>{{ $b->user->name }}</td>
                        <td>{{ $b->jenis_motor }}<br><small>{{ $b->plat_nomor }}</small></td>
                        <td>{{ \App\Models\Booking::LAYANAN[$b->jenis_layanan] ?? $b->jenis_layanan }}</td>
                        <td>{{ $b->keluhan ?: '-' }}</td>
                        <td><span class="badge {{ $b->badgeClass() }}">{{ $b->statusLabel() }}</span></td>
                        @if(auth()->user()->hasRole('mekanik'))
                            <td>
                                <form action="{{ route('pekerjaan.status', $b) }}" method="POST" class="d-flex gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status" class="form-select form-select-sm">
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
                    <tr><td colspan="{{ auth()->user()->hasRole('mekanik') ? 7 : 6 }}" class="text-center py-4">Belum ada pekerjaan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
