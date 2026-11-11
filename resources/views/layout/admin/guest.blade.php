<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Bengkel Motor Cendekia')</title>
    @include('layout.admin.css')
</head>
<body>
    <main>
        @yield('content')
    </main>

    @include('layout.admin.js')
</body>
</html>
