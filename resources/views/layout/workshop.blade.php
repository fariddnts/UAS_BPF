<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Bengkel Syifa')</title>
    <link rel="stylesheet" href="{{ asset('assets/css/base.css') }}">
    @stack('styles')
</head>
<body>
    @yield('body')
    @stack('scripts')
</body>
</html>
