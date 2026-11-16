@extends('layout.admin.guest')

@section('title', 'Daftar - Bengkel Syifa')

@section('content')
<section class="auth-screen auth-screen--register" aria-labelledby="auth-title">
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
            <h1 id="auth-title">Buat Akun</h1>
            <p>Daftar untuk memesan jadwal servis motormu.</p>
        </div>

        @if($errors->any())
            <div class="alert alert-danger" role="alert">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('register.process') }}" method="POST" class="auth-form">
            @csrf

            <div class="mb-3">
                <label class="form-label" for="name">Nama lengkap</label>
                <input type="text" id="name" name="name" class="form-control"
                       value="{{ old('name') }}" placeholder="Masukkan nama lengkap"
                       autocomplete="name" autofocus required>
            </div>

            <div class="mb-3">
                <label class="form-label" for="email">Email</label>
                <input type="email" id="email" name="email" class="form-control"
                       value="{{ old('email') }}" placeholder="Masukkan email kamu"
                       autocomplete="email" required>
            </div>

            <div class="mb-3">
                <label class="form-label" for="password">Kata sandi</label>
                <input type="password" id="password" name="password" class="form-control"
                    placeholder="Buat kata sandi" autocomplete="new-password" required>
                <div class="form-text">Minimal 8 karakter.</div>
            </div>

            <div class="mb-4">
                <label class="form-label" for="password_confirmation">Ulangi kata sandi</label>
                <input type="password" id="password_confirmation" name="password_confirmation"
                    class="form-control" placeholder="Ulangi kata sandi"
                    autocomplete="new-password" required>
            </div>

            <button type="submit" class="btn auth-submit w-100">Daftar</button>
        </form>

        <p class="auth-switch">
            Sudah punya akun? <a href="{{ route('login') }}">Masuk</a>
        </p>
    </div>
</section>
@endsection
