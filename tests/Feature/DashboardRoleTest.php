<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Booking;
use Tests\TestCase;

class DashboardRoleTest extends TestCase
{
    public function test_user_dashboard_shows_the_empty_state_and_animation_link(): void
    {
        $user = User::factory()->make(['role' => User::ROLE_USER]);
        $this->actingAs($user);

        $html = view('dashboard.user', [
            'currentBooking' => null,
            'serviceHistory' => collect(),
        ])->render();

        $this->assertStringContainsString('Belum ada servis berjalan', $html);
        $this->assertStringContainsString('Lihat animasi motor', $html);
    }

    public function test_user_dashboard_displays_the_latest_booking_status(): void
    {
        $user = User::factory()->make(['role' => User::ROLE_USER]);
        $this->actingAs($user);

        $booking = Booking::make([
            'plat_nomor' => 'B 1842 NVT',
            'jenis_motor' => 'Motor',
            'jenis_layanan' => 'ganti_oli',
            'tanggal_booking' => '2026-11-27',
            'jam_booking' => '09:00:00',
            'status' => 'on_progress',
        ]);
        $booking->setRelation('mekanik', null);

        $html = view('dashboard.user', [
            'currentBooking' => $booking,
            'serviceHistory' => collect(),
        ])->render();

        $this->assertStringContainsString('B 1842 NVT', $html);
        $this->assertStringContainsString('Ganti Oli', $html);
        $this->assertStringContainsString('Dikerjakan', $html);
    }

    public function test_role_dashboard_requires_authentication_and_matching_role(): void
    {
        $this->get('/admin/dashboard')->assertRedirect(route('login'));

        $admin = User::factory()->make(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)->get('/owner/dashboard')->assertForbidden();
    }
}
