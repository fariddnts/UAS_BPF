<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#09133d">
    <title>@yield('title', 'Dashboard | Bengkel Syifa')</title>
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard-user.css') }}">
    @stack('styles')
</head>
<body class="user-dashboard-body">
    @yield('content')
    @stack('scripts')
</body>
</html>
