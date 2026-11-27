<!DOCTYPE html>
<html lang="id">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <!-- Primary Meta Tags -->
    <title>@yield('title', 'Panel | Bengkel Syifa')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="title" content="Bengkel Syifa">
    <meta name="author" content="Bengkel Syifa">

    <!-- Favicon -->
    <link rel="apple-touch-icon" sizes="120x120" href="{{ asset('assets-admin/img/favicon/apple-touch-icon.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets-admin/img/favicon/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets-admin/img/favicon/favicon-16x16.png') }}">
    <link rel="manifest" href="{{ asset('assets-admin/img/favicon/site.webmanifest') }}">
    <link rel="mask-icon" href="{{ asset('assets-admin/img/favicon/safari-pinned-tab.svg') }}" color="#ffffff">
    <meta name="msapplication-TileColor" content="#09133d">
    <meta name="theme-color" content="#09133d">

    {{-- Start CSS --}}
    @include('layout.admin.css')
    @stack('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard-navigation.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/workshop-panel.css') }}">
    {{-- End CSS --}}
</head>

<body class="workshop-panel-body">

    @include('layout.dashboard-navigation')

    <main class="workshop-panel-main">
        <div class="workshop-panel-content">
            @yield('content')
        </div>
        @include('layout.admin.footer')
    </main>

    @include('layout.admin.js')
    @stack('scripts')
</body>

</html>
