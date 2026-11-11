@extends('layout.admin.guest')

@section('title', 'Daftar - Bengkel Motor Cendekia')

@section('content')
<section class="d-flex align-items-center my-5 mt-lg-6 mb-lg-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-5">
                <div class="bg-white shadow border-0 rounded p-4 p-lg-5">
                    <h1 class="h3 mb-1">Buat Akun</h1>
                    <p class="text-muted mb-4">Daftar untuk memesan jadwal servis.</p>

                    @if($errors->any())
                        <div class="alert alert-danger" role="alert">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('register.process') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label" for="name">Nama lengkap</label>
                            <input type="text" id="name" name="name" class="form-control"
                                   value="{{ old('name') }}" autocomplete="name" autofocus required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="email">Email</label>
                            <input type="email" id="email" name="email" class="form-control"
                                   value="{{ old('email') }}" autocomplete="email" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="password">Kata sandi</label>
                            <input type="password" id="password" name="password" class="form-control"
                                autocomplete="new-password" required>
                            <div class="form-text">Minimal 8 karakter.</div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label" for="password_confirmation">Ulangi kata sandi</label>
                            <input type="password" id="password_confirmation" name="password_confirmation"
                                class="form-control" autocomplete="new-password" required>
                        </div>

                        <button type="submit" class="btn btn-gray-800 w-100">Daftar</button>
                    </form>

                    <p class="text-center mt-4 mb-0">
                        Sudah punya akun? <a href="{{ route('login') }}">Masuk</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
