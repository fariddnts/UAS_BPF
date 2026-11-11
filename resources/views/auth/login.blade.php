@extends('layout.admin.guest')

@section('title', 'Masuk - Bengkel Motor Cendekia')

@section('content')
<section class="d-flex align-items-center my-5 mt-lg-6 mb-lg-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-5">
                <div class="bg-white shadow border-0 rounded p-4 p-lg-5">
                    <h1 class="h3 mb-1">Masuk</h1>
                    <p class="text-muted mb-4">Bengkel Motor Cendekia</p>

                    @if($errors->any())
                        <div class="alert alert-danger" role="alert">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <form action="{{ route('login.process') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label" for="email">Email</label>
                            <input type="email" id="email" name="email" class="form-control"
                                   value="{{ old('email') }}" autocomplete="email" autofocus required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="password">Kata sandi</label>
                            <input type="password" id="password" name="password" class="form-control"
                                   autocomplete="current-password" required>
                        </div>

                        <div class="d-flex justify-content-between mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="show-password"
                                    onclick="document.getElementById('password').type = this.checked ? 'text' : 'password'">
                                <label class="form-check-label" for="show-password">Tampilkan kata sandi</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="remember" name="remember">
                                <label class="form-check-label" for="remember">Ingat saya</label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-gray-800 w-100">Masuk</button>
                    </form>

                    <p class="text-center mt-4 mb-0">
                        Belum punya akun? <a href="{{ route('register') }}">Daftar</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
