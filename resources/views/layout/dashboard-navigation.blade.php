<header class="dashboard-topbar">
    <a class="dashboard-topbar__brand" href="{{ route('dashboard') }}">
        <svg viewBox="0 0 32 32" fill="none" aria-hidden="true">
            <path d="M16 2 28 9v14l-12 7L4 23V9z" stroke="currentColor" stroke-width="2"/>
            <circle cx="16" cy="16" r="5.5" stroke="currentColor" stroke-width="2"/>
            <path d="M1 16h30" stroke="currentColor" stroke-width="2"/>
        </svg>
        <span>Bengkel Syifa</span>
    </a>

    <nav class="dashboard-topbar__nav" aria-label="Navigasi dashboard">
        @if(auth()->user()->hasRole('user'))
            @php
                $serviceLink = request()->routeIs('dashboard', 'user.dashboard')
                    ? '#service-status'
                    : route('user.dashboard', [], false).'#service-status';
            @endphp
            <a class="{{ request()->routeIs('dashboard', 'user.dashboard') ? 'is-active' : '' }}" href="{{ $serviceLink }}">Servis saya</a>
            <a class="{{ request()->routeIs('booking.create') ? 'is-active' : '' }}" href="{{ route('booking.create', [], false) }}">Booking</a>
        @else
            <a class="{{ request()->routeIs('dashboard', '*.dashboard') ? 'is-active' : '' }}" href="{{ route('dashboard') }}">
                {{ auth()->user()->hasRole('mekanik') ? 'Pekerjaan' : 'Dashboard' }}
            </a>
            @if(auth()->user()->hasRole('admin'))
                <a class="{{ request()->routeIs('admin.completed-users') ? 'is-active' : '' }}" href="{{ route('admin.completed-users') }}">User selesai</a>
                <a class="{{ request()->routeIs('user.*') ? 'is-active' : '' }}" href="{{ route('user.index') }}">Kelola akun</a>
            @endif
            @if(auth()->user()->hasRole('owner'))
                <a class="{{ request()->routeIs('pelanggan.*') ? 'is-active' : '' }}" href="{{ route('pelanggan.index') }}">Data pelanggan</a>
            @endif
        @endif
    </nav>

    <div class="dashboard-topbar__profile">
        <span class="dashboard-topbar__avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
        <span class="dashboard-topbar__identity">
            <strong>{{ auth()->user()->name }}</strong>
            <small>{{ ucfirst(auth()->user()->role) }}</small>
        </span>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit">Keluar</button>
        </form>
    </div>
</header>
