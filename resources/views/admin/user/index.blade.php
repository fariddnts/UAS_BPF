@extends('layout.admin.app')

@section('title', 'Daftar User')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/admin-users.css') }}">
@endpush

@section('content')
    @php
        $usersByRole = $dataUser->groupBy('role');
        $roleStyles = [
            \App\Models\User::ROLE_OWNER => 'owner',
            \App\Models\User::ROLE_ADMIN => 'admin',
            \App\Models\User::ROLE_MEKANIK => 'mekanik',
            \App\Models\User::ROLE_USER => 'user',
        ];
    @endphp

    <div class="admin-users">
    <div class="py-4">
        <nav aria-label="breadcrumb" class="d-none d-md-inline-block">
            <ol class="breadcrumb breadcrumb-dark breadcrumb-transparent">
                <li class="breadcrumb-item">
                    <a href="{{ route('dashboard') }}">
                        <svg class="icon icon-xxs" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                        </svg>
                    </a>
                </li>
                <li class="breadcrumb-item"><a href="{{ route('user.index') }}">User</a></li>
                <li class="breadcrumb-item active" aria-current="page">Daftar User</li>
            </ol>
        </nav>
        <div class="d-flex justify-content-between w-100 flex-wrap">
            <div class="mb-3 mb-lg-0">
                <p class="admin-users__eyebrow">Bengkel Syifa · Panel Admin</p>
                <h1 class="admin-users__title">Manajemen Pengguna</h1>
                <p class="admin-users__subtitle">Kelola akun, akses, dan peran pengguna bengkel.</p>
            </div>
            <div>
                <a href="{{ route('user.create') }}" class="btn admin-users__add-button d-inline-flex align-items-center">
                    Tambah User
                </a>
            </div>
        </div>
    </div>

    @include('layout.admin.alert')

    <section class="admin-users__summary" aria-label="Ringkasan pengguna berdasarkan role">
        <article class="admin-users__stat admin-users__stat--all">
            <span class="admin-users__stat-icon" aria-hidden="true">◎</span>
            <div><p>Total Akun</p><strong>{{ $dataUser->count() }}</strong><span>pengguna</span></div>
        </article>
        @foreach(\App\Models\User::ROLES as $role)
            <article class="admin-users__stat admin-users__stat--{{ $roleStyles[$role] }}">
                <span class="admin-users__stat-icon" aria-hidden="true">
                    @switch($role)
                        @case(\App\Models\User::ROLE_OWNER) ★ @break
                        @case(\App\Models\User::ROLE_ADMIN) ⚙ @break
                        @case(\App\Models\User::ROLE_MEKANIK) 🔧 @break
                        @default ●
                    @endswitch
                </span>
                <div>
                    <p>{{ ucfirst($role) }}</p>
                    <strong>{{ $usersByRole->get($role, collect())->count() }}</strong>
                    <span>akun</span>
                </div>
            </article>
        @endforeach
    </section>

    <section class="admin-users__list" aria-labelledby="admin-users-list-title">
        <div class="admin-users__list-heading">
            <div>
                <p class="admin-users__eyebrow">Akun terdaftar</p>
                <h2 id="admin-users-list-title">Daftar User</h2>
            </div>
            <span class="admin-users__count">{{ $dataUser->count() }} akun</span>
        </div>

        <div class="card border-0 shadow admin-users__table-card">
            <div class="table-responsive">
                <table class="table table-centered mb-0 admin-users__table">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($usersByRole as $role => $users)
                            <tr class="admin-users__role-row admin-users__role-row--{{ $roleStyles[$role] ?? 'other' }}">
                                <th colspan="4" scope="rowgroup">
                                    <span class="admin-users__role-marker"></span>
                                    {{ ucfirst($role) }}
                                    <span class="admin-users__role-count">{{ $users->count() }}</span>
                                </th>
                            </tr>
                            @foreach ($users as $item)
                                <tr>
                                    <td>
                                        <span class="admin-users__avatar" aria-hidden="true">{{ strtoupper(substr($item->name, 0, 1)) }}</span>
                                        <strong class="admin-users__name">{{ $item->name }}</strong>
                                        @if ($item->id === auth()->id())
                                            <span class="badge bg-success ms-1">Anda</span>
                                        @endif
                                    </td>
                                    <td>{{ $item->email }}</td>
                                    <td><span class="admin-users__role-badge admin-users__role-badge--{{ $roleStyles[$item->role] ?? 'other' }}">{{ ucfirst($item->role) }}</span></td>
                                    <td class="admin-users__actions">
                                        <a href="{{ route('user.edit', $item->id) }}" class="btn btn-sm admin-users__edit-button">Edit</a>
                                        @if ($item->id !== auth()->id())
                                            <form action="{{ route('user.destroy', $item->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm admin-users__delete-button" onclick="return confirm('Yakin mau hapus data ini?')">Hapus</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        @empty
                            <tr>
                                <td colspan="4" class="admin-users__empty">Belum ada data user.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>
    </div>
@endsection
