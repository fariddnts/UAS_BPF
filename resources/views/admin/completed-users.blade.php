@extends('layout.admin.app')

@section('title', 'User Selesai | Bengkel Syifa')

@section('content')
    <div class="py-4">
        <div class="d-flex justify-content-between w-100 flex-wrap">
            <div class="mb-3 mb-lg-0">
                <h1 class="h4">Data User Selesai</h1>
                <p class="mb-0">Daftar servis QRIS yang pembayarannya telah disetujui admin.</p>
            </div>
            <span class="badge bg-success align-self-start">{{ $bookings->count() }} servis selesai</span>
        </div>
    </div>

    <div class="row">
        <div class="col-12 mb-4">
            <div class="card border-0 shadow">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-centered table-nowrap mb-0 rounded">
                            <thead class="thead-light">
                                <tr>
                                    <th>Nama user</th>
                                    <th>Email</th>
                                    <th>Motor</th>
                                    <th>Layanan</th>
                                    <th>Tanggal servis</th>
                                    <th>Total biaya</th>
                                    <th>Disetujui pada</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($bookings as $booking)
                                    <tr>
                                        <td>{{ $booking->user->name }}</td>
                                        <td>{{ $booking->user->email }}</td>
                                        <td>
                                            {{ $booking->jenis_motor }}
                                            <small class="d-block text-muted">{{ $booking->plat_nomor }}</small>
                                        </td>
                                        <td>{{ \App\Models\Booking::LAYANAN[$booking->jenis_layanan] ?? $booking->jenis_layanan }}</td>
                                        <td>{{ $booking->tanggal_booking->format('d M Y') }}</td>
                                        <td>Rp{{ number_format((int) $booking->total_biaya, 0, ',', '.') }}</td>
                                        <td>{{ $booking->payment_verified_at->format('d M Y H:i') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4">Belum ada user dengan servis yang telah disetujui.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
