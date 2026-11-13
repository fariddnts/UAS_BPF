@extends('layout.admin.app')

@section('title', 'Ringkasan Servis')

@section('content')
<div class="py-4">
    <h1 class="h4 mb-1">Ringkasan Servis</h1>
    <p class="text-muted mb-0">Jumlah pesanan servis berdasarkan waktu pemesanan.</p>
</div>

<div class="row">
    @foreach ($bookingStats as $key => $stats)
        <div class="col-12 col-md-4 mb-4">
            <div class="card border-0 shadow h-100">
                <div class="card-body">
                    <h2 class="h6 text-gray-500 text-uppercase">{{ $stats['label'] }}</h2>
                    <p class="display-6 fw-bold text-gray-900 mb-0">{{ number_format($stats['total']) }}</p>
                    <p class="text-muted mb-0">pesanan servis</p>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="card border-0 shadow mb-4">
    <div class="card-header">
        <h2 class="h5 mb-0">Jenis Servis</h2>
    </div>
    <div class="table-responsive">
        <table class="table table-centered mb-0">
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
                        <th scope="row">{{ $serviceLabel }}</th>
                        <td>{{ number_format($bookingStats['daily']['services'][$serviceKey]) }}</td>
                        <td>{{ number_format($bookingStats['monthly']['services'][$serviceKey]) }}</td>
                        <td>{{ number_format($bookingStats['yearly']['services'][$serviceKey]) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
