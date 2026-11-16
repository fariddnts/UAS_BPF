@extends('layout.admin.guest')

@section('title', 'Masuk - Bengkel Syifa')

@section('content')
<section class="auth-screen" aria-labelledby="auth-title">
    <div class="auth-card">
        <a class="auth-brand" href="{{ route('login') }}" aria-label="Bengkel Syifa">
            <span class="auth-brand__mark" aria-hidden="true">
                <svg viewBox="0 0 32 32" fill="none">
                    <circle cx="16" cy="16" r="12" stroke="currentColor" stroke-width="3"/>
                    <circle cx="16" cy="16" r="4" fill="currentColor"/>
                    <path d="M16 4v8m12 4h-8M16 28v-8M4 16h8" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
                </svg>
            </span>
            <span>Bengkel <strong>Syifa</strong></span>
        </a>
        <div class="auth-card__heading">
            <h1 id="auth-title">Selamat Datang</h1>
            <p>Masuk untuk mengelola jadwal servis motormu.</p>
        </div>

        @if(session('success'))
            <div class="alert alert-success" role="alert">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger" role="alert">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('login.process') }}" method="POST" class="auth-form">
            @csrf

            <div class="mb-3">
                <label class="form-label" for="email">Email</label>
                <input type="email" id="email" name="email" class="form-control"
                       value="{{ old('email') }}" placeholder="Masukkan email kamu"
                       autocomplete="email" autofocus required>
            </div>

            <div class="mb-3">
                <label class="form-label" for="password">Kata sandi</label>
                <input type="password" id="password" name="password" class="form-control"
                       placeholder="Masukkan kata sandi" autocomplete="current-password" required>
            </div>

            <div class="auth-form__options">
                <label class="form-check">
                    <input class="form-check-input" type="checkbox" id="show-password"
                           onclick="document.getElementById('password').type = this.checked ? 'text' : 'password'">
                    <span class="form-check-label">Tampilkan kata sandi</span>
                </label>
                <label class="form-check">
                    <input class="form-check-input" type="checkbox" id="remember" name="remember">
                    <span class="form-check-label">Ingat saya</span>
                </label>
            </div>

            <button type="submit" class="btn auth-submit w-100">Masuk</button>
        </form>

        <p class="auth-switch">
            Belum punya akun? <a href="{{ route('register') }}">Daftar sekarang</a>
        </p>
    </div>
</section>
@endsection
