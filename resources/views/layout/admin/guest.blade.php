<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Bengkel Motor Cendekia')</title>
    @include('layout.admin.css')
    <link type="text/css" href="{{ asset('assets/css/auth.css') }}" rel="stylesheet">
</head>
<body class="auth-layout">
    <main class="auth-layout__main">
        @yield('content')
    </main>

    @include('layout.admin.js')
</body>
</html>
