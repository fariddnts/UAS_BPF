@extends('layout.workshop')

@section('title', 'Bengkel Syifa | Servis Motor')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/landing.css') }}">
@endpush

@push('scripts')
    <script src="{{ asset('assets/js/moto3d.js') }}"></script>
    <script src="{{ asset('assets/js/landing.js') }}"></script>
@endpush

@section('body')
<canvas id="moto" aria-hidden="true"></canvas>

<header class="nav">
  <a class="brand" href="#atas"><svg viewBox="0 0 32 32" fill="none" aria-hidden="true"><path d="M16 2l12.1 7v14L16 30 3.9 23V9z" stroke="#eef3ff" stroke-width="2"/><circle cx="16" cy="16" r="5.5" stroke="#52e0ff" stroke-width="2"/><path d="M1 16h30" stroke="#52e0ff" stroke-width="2"/></svg>Bengkel Syifa</a>
  <nav aria-label="Bagian halaman">
    <a href="#pindai">Pindai kendaraan</a>
    <a href="#alur">Alur servis</a>
  </nav>
  <div class="nav-actions">
    <a class="btn btn-sm" href="{{ Route::has('login') ? route('login') : '#' }}">Masuk</a>
    <a class="btn btn-sm btn-primary" href="{{ route('booking.create') }}">Booking servis</a>
  </div>
  <div class="nav-progress"><i></i></div>
</header>

<main id="atas">
  <section class="hero">
    <div class="hero-copy">
      <h1>
        <span class="ln"><span>Motor Anda,</span></span>
        <span class="ln"><span>dipindai sampai</span></span>
        <span class="ln"><span>ke bautnya.</span></span>
      </h1>
      <p class="fade">Booking dari ponsel, pantau mekanik bekerja secara langsung, dan setujui biaya sebelum ada sparepart yang diganti.</p>
      <div class="actions fade">
        <a class="btn btn-primary" href="{{ route('booking.create') }}">Booking servis</a>
        <a class="btn" href="#pindai">Lihat cara kami memeriksa</a>
      </div>
    </div>
    <p class="scroll-hint fade"><i></i>Scroll untuk memindai</p>
  </section>

  <section class="scan" id="pindai">
    <div class="stick">
      <div class="scan-intro">
        <h2>Empat sistem diperiksa dalam satu pemindaian</h2>
      </div>
      <ol class="stages">
        <li><b>Mesin</b><span>Suhu, oli, dan pendinginan</span><i></i></li>
        <li><b>Rem</b><span>Kampas, cakram, dan minyak</span><i></i></li>
        <li><b>Kaki-kaki</b><span>Ban, rantai, dan suspensi</span><i></i></li>
        <li><b>Kelistrikan</b><span>Aki, pengisian, dan sensor</span><i></i></li>
      </ol>
      <div class="readings">
        <article data-anchor="0.5,1.42,-0.16">
          <header><h3>Mesin</h3><span class="status good"><i>✓</i>Baik</span></header>
          <dl>
            <div><dt>Suhu mesin</dt><dd data-count="92" data-suffix=" °C">92 °C</dd></div>
            <div><dt>Putaran stasioner</dt><dd data-count="1400" data-suffix=" rpm">1.400 rpm</dd></div>
            <div><dt>Sisa umur oli</dt><dd data-count="64" data-suffix="%">64%</dd></div>
          </dl>
          <p>Ganti oli berikutnya sekitar 1.200 km lagi.</p>
        </article>
        <article data-anchor="1.4,0.96,-0.17">
          <header><h3>Rem</h3><span class="status warn"><i>!</i>Perlu diganti</span></header>
          <dl>
            <div><dt>Kampas depan <small>batas 2 mm</small></dt><dd data-count="1.2" data-dec="1" data-suffix=" mm">1,2 mm</dd></div>
            <div><dt>Kampas belakang</dt><dd data-count="3.4" data-dec="1" data-suffix=" mm">3,4 mm</dd></div>
            <div><dt>Kadar air minyak rem</dt><dd data-count="1.4" data-dec="1" data-suffix="%">1,4%</dd></div>
          </dl>
          <p>Ganti kampas rem depan pada servis ini.</p>
        </article>
        <article data-anchor="-0.69,1.11,-0.11">
          <header><h3>Kaki-kaki</h3><span class="status good"><i>✓</i>Baik</span></header>
          <dl>
            <div><dt>Tekanan ban belakang</dt><dd data-count="33" data-suffix=" psi">33 psi</dd></div>
            <div><dt>Jarak main rantai <small>standar 20–30 mm</small></dt><dd data-count="25" data-suffix=" mm">25 mm</dd></div>
            <div><dt>Kedalaman alur ban</dt><dd data-count="3.8" data-dec="1" data-suffix=" mm">3,8 mm</dd></div>
          </dl>
          <p>Lumasi rantai pada servis berikutnya.</p>
        </article>
        <article data-anchor="-0.47,1.28,-0.16">
          <header><h3>Kelistrikan</h3><span class="status warn"><i>!</i>Pantau</span></header>
          <dl>
            <div><dt>Tegangan aki <small>ideal 12,4 V</small></dt><dd data-count="12.1" data-dec="1" data-suffix=" V">12,1 V</dd></div>
            <div><dt>Tegangan pengisian</dt><dd data-count="14.1" data-dec="1" data-suffix=" V">14,1 V</dd></div>
            <div><dt>Kode kesalahan</dt><dd data-count="0">0</dd></div>
          </dl>
          <p>Cek ulang aki dalam satu bulan.</p>
        </article>
      </div>
      <div class="pin" aria-hidden="true"><i></i><span></span></div>
    </div>
  </section>

  <section class="flow" id="alur">
    <div class="stick">
      <h2>Dari booking sampai kunci kembali</h2>
      <div class="flow-line"><i></i><b></b></div>
      <ol class="track">
        <li class="step">
          <span class="num">1</span>
          <h3>Booking</h3>
          <p>Pelanggan memilih jadwal dan menuliskan keluhan.</p>
          <div class="mock">
            <div class="chips"><span data-at="0.1">Sab, 28 Nov</span><span data-at="0.25" class="on">09.00</span><span data-at="0.35">10.30</span><span data-at="0.45">13.00</span></div>
            <div class="row" data-at="0.6"><span>Keluhan</span><b>Rem depan berdecit</b></div>
            <div class="row" data-at="0.75"><span>Motor</span><b>B 1842 NVT</b></div>
          </div>
        </li>
        <li class="step">
          <span class="num">2</span>
          <h3>Diagnosa</h3>
          <p>Mekanik memeriksa empat sistem dan mencatat hasilnya.</p>
          <div class="mock">
            <div class="row" data-at="0.2"><span>Mesin</span><span class="status good"><i>✓</i>Baik</span></div>
            <div class="row" data-at="0.35"><span>Rem</span><span class="status warn"><i>!</i>Perlu diganti</span></div>
            <div class="row" data-at="0.5"><span>Kaki-kaki</span><span class="status good"><i>✓</i>Baik</span></div>
            <div class="row" data-at="0.65"><span>Kelistrikan</span><span class="status warn"><i>!</i>Pantau</span></div>
          </div>
        </li>
        <li class="step">
          <span class="num">3</span>
          <h3>Pengerjaan</h3>
          <p>Setelah biaya disetujui, progres tampil secara langsung.</p>
          <div class="mock">
            <div class="meter"><i data-fill="68"></i></div>
            <div class="row" data-at="0.25"><span>10.42</span><b>Kaliper depan dilepas</b></div>
            <div class="row" data-at="0.45"><span>10.50</span><b>Kampas lama: 1,2 mm</b></div>
            <div class="row" data-at="0.65"><span>10.58</span><b>Kampas baru dipasang</b></div>
          </div>
        </li>
        <li class="step">
          <span class="num">4</span>
          <h3>Serah terima</h3>
          <p>Tagihan sama dengan estimasi yang sudah disetujui.</p>
          <div class="mock">
            <div class="row" data-at="0.2"><span>Kampas rem depan</span><b>Rp65.000</b></div>
            <div class="row" data-at="0.32"><span>Minyak rem</span><b>Rp25.000</b></div>
            <div class="row" data-at="0.44"><span>Jasa pengerjaan</span><b>Rp35.000</b></div>
            <div class="row total" data-at="0.62"><span>Total</span><b>Rp125.000</b></div>
          </div>
        </li>
      </ol>
    </div>
  </section>

  <section class="finale">
    <div class="stick">
      <h2>Servis tanpa tebak-tebakan.</h2>
      <a class="btn btn-primary" href="{{ route('booking.create') }}">Booking servis</a>
      <p class="note">Bengkel Syifa melayani servis motor dengan transparan. Data demo di halaman ini bersifat ilustrasi.</p>
    </div>
  </section>
</main>
@endsection
