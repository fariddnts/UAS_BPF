{{-- Start Header --}}
<nav class="navbar navbar-top navbar-expand navbar-dashboard navbar-dark ps-0 pe-2 pb-0">
    <div class="container-fluid px-0">
        <div class="d-flex {{ auth()->user()->hasRole('user') ? 'justify-content-between' : 'justify-content-end' }} w-100" id="navbarSupportedContent">
            @if(auth()->user()->hasRole('user'))
                <div class="user-header__navigation">
                    <a class="user-header__brand" href="{{ route('dashboard') }}">Bengkel Syifa</a>
                    <a class="user-header__link {{ request()->routeIs('dashboard') ? 'is-active' : '' }}" href="{{ route('dashboard') }}">Beranda</a>
                    <a class="user-header__link {{ request()->routeIs('booking.create') ? 'is-active' : '' }}" href="{{ route('booking.create') }}">Pesan Jadwal</a>
                </div>
            @endif

            {{-- Navbar links --}}
            <ul class="navbar-nav align-items-center">
                <li class="nav-item dropdown ms-lg-3">
                    <a class="nav-link dropdown-toggle pt-1 px-0" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="media d-flex align-items-center">
                            <img class="avatar rounded-circle" alt="Foto profil default" src="{{ asset('assets-admin/img/default-avatar.svg') }}">
                            <div class="media-body ms-2 text-dark align-items-center d-none d-lg-block">
                                <span class="mb-0 font-small fw-bold text-gray-900 d-block">{{ auth()->user()->name }}</span>
                                <span class="font-small text-gray-600">{{ ucfirst(auth()->user()->role) }}</span>
                            </div>
                        </div>
                    </a>
                    <div class="dropdown-menu dashboard-dropdown dropdown-menu-end mt-2 py-1">
                        <div class="px-3 py-2 d-lg-none">
                            <span class="d-block fw-bold">{{ auth()->user()->name }}</span>
                            <span class="small text-gray-600">{{ ucfirst(auth()->user()->role) }}</span>
                        </div>
                        <div role="separator" class="dropdown-divider my-1 d-lg-none"></div>

                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="dropdown-item d-flex align-items-center w-100">
                                <svg class="dropdown-icon text-danger me-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                </svg>
                                Logout
                            </button>
                        </form>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</nav>
{{-- End Header --}}
