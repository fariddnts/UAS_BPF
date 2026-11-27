<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Booking;
use Tests\TestCase;

class DashboardRoleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withViewErrors([]);
    }

    public function test_user_dashboard_shows_the_empty_state_and_back_link_without_extra_navigation_items(): void
    {
        $user = User::factory()->make(['role' => User::ROLE_USER]);
        $this->actingAs($user);

        $html = view('dashboard.user', [
            'currentBooking' => null,
            'estimateBooking' => null,
            'serviceHistory' => collect(),
        ])->render();

        $this->assertStringContainsString('Belum ada servis berjalan', $html);
        $this->assertStringContainsString('Kembali', $html);
        $this->assertStringNotContainsString('Lihat animasi motor', $html);
        $this->assertStringNotContainsString('Jelajahi motor', $html);
        $this->assertStringNotContainsString('customer-animation', $html);
        $this->assertStringNotContainsString('>Riwayat</a>', $html);
        $this->assertStringNotContainsString('>Kendaraan</a>', $html);
        $this->assertStringContainsString('Dari booking sampai kunci kembali', $html);
        $this->assertStringContainsString('Diagnosa', $html);
        $this->assertStringContainsString('Serah terima', $html);
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
            'estimateBooking' => $booking,
            'serviceHistory' => collect(),
        ])->render();

        $this->assertStringContainsString('B 1842 NVT', $html);
        $this->assertStringContainsString('Ganti Oli', $html);
        $this->assertStringContainsString('Pengerjaan', $html);
        $this->assertStringContainsString('Pemeriksaan mencakup mesin, rem, kaki-kaki, dan kelistrikan.', $html);
        $this->assertStringContainsString('Hasil diagnosa terperinci belum tersedia di sistem.', $html);
        $this->assertStringNotContainsString('Rp125.000', $html);
    }

    public function test_finished_booking_shows_handover_without_inventing_a_bill(): void
    {
        $user = User::factory()->make(['role' => User::ROLE_USER]);
        $this->actingAs($user);

        $booking = Booking::make([
            'plat_nomor' => 'B 1842 NVT',
            'jenis_motor' => 'Motor',
            'jenis_layanan' => 'ganti_oli',
            'tanggal_booking' => '2026-11-27',
            'jam_booking' => '09:00:00',
            'status' => 'finished',
        ]);
        $booking->setRelation('mekanik', null);

        $html = view('dashboard.user', [
            'currentBooking' => $booking,
            'estimateBooking' => $booking,
            'serviceHistory' => collect(),
        ])->render();

        $this->assertStringContainsString('width: 100%', $html);
        $this->assertStringContainsString('Servis selesai. Mekanik belum mencatat total biaya.', $html);
        $this->assertStringContainsString('Servis selesai, tetapi total biaya belum dicatat mekanik.', $html);
        $this->assertStringNotContainsString('Rp125.000', $html);
    }

    public function test_completed_service_estimate_shows_cash_and_qris_payment_without_history_estimate_button(): void
    {
        $user = User::factory()->make(['role' => User::ROLE_USER]);
        $this->actingAs($user);

        $booking = Booking::make([
            'plat_nomor' => 'B 1842 NVT',
            'jenis_motor' => 'Motor',
            'jenis_layanan' => 'ganti_oli',
            'tanggal_booking' => '2026-11-27',
            'jam_booking' => '09:00:00',
            'status' => 'finished',
            'total_biaya' => 125000,
            'metode_pembayaran' => 'cash',
        ]);
        $booking->setAttribute('id', 35);
        $booking->setRelation('mekanik', null);

        $html = view('dashboard.user', [
            'currentBooking' => $booking,
            'estimateBooking' => $booking,
            'serviceHistory' => collect([$booking]),
        ])->render();

        $this->assertStringContainsString('Rp125.000', $html);
        $this->assertStringContainsString('value="cash"', $html);
        $this->assertStringContainsString('checked required', $html);
        $this->assertStringContainsString('value="qris"', $html);
        $this->assertStringNotContainsString('>OK</button>', $html);
        $this->assertStringContainsString('Silakan lakukan pembayaran ke kasir bengkel.', $html);
        $this->assertStringNotContainsString('Lihat estimasi', $html);

        $booking->setAttribute('metode_pembayaran', 'qris');
        $qrisHtml = view('dashboard.user', [
            'currentBooking' => $booking,
            'estimateBooking' => $booking,
            'serviceHistory' => collect([$booking]),
        ])->render();

        $this->assertStringContainsString('checked required', $qrisHtml);
        $this->assertStringContainsString('Pilih QRIS untuk membuka halaman pembayaran', $qrisHtml);
        $this->assertStringContainsString('href="/booking/35/payment/qris"', $qrisHtml);
        $this->assertStringContainsString('Buka Halaman Pembayaran QRIS', $qrisHtml);
        $this->assertStringNotContainsString('name="bukti_tf"', $qrisHtml);
    }

    public function test_qris_payment_page_shows_qr_upload_and_payment_status(): void
    {
        $user = User::factory()->make(['role' => User::ROLE_USER]);
        $this->actingAs($user);

        $booking = Booking::make([
            'plat_nomor' => 'B 1842 NVT',
            'jenis_motor' => 'Motor',
            'jenis_layanan' => 'ganti_oli',
            'tanggal_booking' => '2026-11-27',
            'jam_booking' => '09:00:00',
            'status' => 'finished',
            'total_biaya' => 125000,
            'metode_pembayaran' => 'qris',
        ]);
        $booking->setAttribute('id', 35);
        $booking->setAttribute('payment_proof_path', 'payment-proofs/bukti.jpg');

        $pendingHtml = view('dashboard.payment-qris', ['booking' => $booking])->render();

        $this->assertStringContainsString('Scan QRIS berikut untuk membayar.', $pendingHtml);
        $this->assertStringContainsString('alt="QRIS pembayaran Bengkel Syifa"', $pendingHtml);
        $this->assertStringContainsString('action="/booking/35/payment-proof"', $pendingHtml);
        $this->assertStringContainsString('enctype="multipart/form-data"', $pendingHtml);
        $this->assertStringContainsString('name="bukti_tf"', $pendingHtml);
        $this->assertStringContainsString('Bukti transfer diterima; menunggu verifikasi admin.', $pendingHtml);
        $this->assertStringContainsString('Kirim ulang bukti', $pendingHtml);
        $this->assertStringContainsString('Belum siap', $pendingHtml);

        $booking->setAttribute('payment_verified_at', now());
        $verifiedHtml = view('dashboard.payment-qris', ['booking' => $booking])->render();

        $this->assertStringContainsString('Servis selesai', $verifiedHtml);
        $this->assertStringNotContainsString('name="bukti_tf"', $verifiedHtml);
    }

    public function test_qris_service_stays_not_ready_until_admin_verifies_payment(): void
    {
        $user = User::factory()->make(['role' => User::ROLE_USER]);
        $this->actingAs($user);

        $booking = Booking::make([
            'plat_nomor' => 'B 1842 NVT',
            'jenis_motor' => 'Motor',
            'jenis_layanan' => 'ganti_oli',
            'tanggal_booking' => '2026-11-27',
            'jam_booking' => '09:00:00',
            'status' => 'finished',
            'total_biaya' => 125000,
            'metode_pembayaran' => 'qris',
            'payment_proof_path' => 'payment-proofs/bukti.jpg',
        ]);
        $booking->setAttribute('id', 35);
        $booking->setRelation('mekanik', null);

        $pendingHtml = view('dashboard.user', [
            'currentBooking' => $booking,
            'estimateBooking' => $booking,
            'serviceHistory' => collect([$booking]),
        ])->render();

        $this->assertStringContainsString('Belum siap', $pendingHtml);
        $this->assertStringContainsString('Buka Halaman Pembayaran QRIS', $pendingHtml);
        $this->assertStringNotContainsString('Pembayaran terverifikasi. Servis selesai.', $pendingHtml);

        $booking->setAttribute('payment_verified_at', now());
        $verifiedHtml = view('dashboard.user', [
            'currentBooking' => $booking,
            'estimateBooking' => $booking,
            'serviceHistory' => collect([$booking]),
        ])->render();

        $this->assertStringContainsString('Servis selesai', $verifiedHtml);
        $this->assertStringContainsString('Pembayaran QRIS telah diverifikasi.', $verifiedHtml);
        $this->assertStringNotContainsString('name="bukti_tf"', $verifiedHtml);
        $this->assertStringNotContainsString('Belum siap', $verifiedHtml);
    }

    public function test_admin_can_review_uploaded_qris_payment_from_work_list(): void
    {
        $admin = User::factory()->make(['role' => User::ROLE_ADMIN]);
        $customer = User::factory()->make(['role' => User::ROLE_USER]);
        $this->actingAs($admin);
        $booking = Booking::make([
            'plat_nomor' => 'B 1842 NVT',
            'jenis_motor' => 'Motor',
            'jenis_layanan' => 'ganti_oli',
            'tanggal_booking' => '2026-11-27',
            'jam_booking' => '09:00:00',
            'status' => 'finished',
            'total_biaya' => 125000,
            'metode_pembayaran' => 'qris',
            'payment_proof_path' => 'payment-proofs/bukti.jpg',
        ]);
        $booking->setAttribute('id', 35);
        $booking->setRelation('user', $customer);

        $html = view('dashboard.mekanik', ['bookings' => collect([$booking])])->render();

        $this->assertStringContainsString('Verifikasi pembayaran', $html);
        $this->assertStringContainsString('href="/admin/bookings/35/payment-proof"', $html);
        $this->assertStringContainsString('name="verified"', $html);
        $this->assertStringContainsString('name="_method" value="PATCH"', $html);

        $booking->setAttribute('payment_verified_at', now());
        $verifiedHtml = view('dashboard.mekanik', ['bookings' => collect([$booking])])->render();
        $this->assertStringContainsString('name="verified" value="1" checked', $verifiedHtml);
    }

    public function test_admin_completed_users_page_lists_verified_service_records(): void
    {
        $admin = User::factory()->make(['role' => User::ROLE_ADMIN]);
        $customer = User::factory()->make([
            'name' => 'Asep Pelanggan',
            'email' => 'asep@example.test',
        ]);
        $this->actingAs($admin);

        $booking = Booking::make([
            'plat_nomor' => 'B 1842 NVT',
            'jenis_motor' => 'Motor',
            'jenis_layanan' => 'ganti_oli',
            'tanggal_booking' => '2026-11-27',
            'status' => 'finished',
            'total_biaya' => 125000,
            'metode_pembayaran' => 'qris',
            'payment_verified_at' => now(),
        ]);
        $booking->setRelation('user', $customer);

        $html = view('admin.completed-users', ['bookings' => collect([$booking])])->render();

        $this->assertStringContainsString('Data User Selesai', $html);
        $this->assertStringContainsString('Asep Pelanggan', $html);
        $this->assertStringContainsString('asep@example.test', $html);
        $this->assertStringContainsString('Rp125.000', $html);
        $this->assertStringContainsString('Disetujui pada', $html);
    }

    public function test_admin_navigation_includes_completed_users_menu(): void
    {
        $admin = User::factory()->make(['role' => User::ROLE_ADMIN]);
        $this->actingAs($admin);

        $html = view('layout.dashboard-navigation')->render();

        $this->assertStringContainsString('User selesai', $html);
        $this->assertStringContainsString('/admin/completed-users', $html);
        $this->assertLessThan(strpos($html, 'Kelola akun'), strpos($html, 'User selesai'));
    }

    public function test_diagnosed_booking_marks_the_diagnosis_stage_current(): void
    {
        $user = User::factory()->make(['role' => User::ROLE_USER]);
        $this->actingAs($user);

        $booking = Booking::make([
            'plat_nomor' => 'B 1842 NVT',
            'jenis_motor' => 'Motor',
            'jenis_layanan' => 'ganti_oli',
            'tanggal_booking' => '2026-11-27',
            'jam_booking' => '09:00:00',
            'status' => 'diagnosed',
        ]);
        $booking->setRelation('mekanik', null);

        $html = view('dashboard.user', [
            'currentBooking' => $booking,
            'estimateBooking' => $booking,
            'serviceHistory' => collect(),
        ])->render();

        $this->assertStringContainsString('customer-process__card is-current', $html);
        $this->assertStringContainsString('Diagnosa sedang dilakukan', $html);
    }

    public function test_booking_page_uses_the_customer_dashboard_navigation_and_theme(): void
    {
        $user = User::factory()->make(['role' => User::ROLE_USER]);
        $this->actingAs($user);
        $this->withViewErrors([]);

        $html = view('dashboard.booking', [
            'bookings' => collect(),
            'selectedService' => '',
        ])->render();

        $this->assertStringContainsString('customer-dashboard', $html);
        $this->assertStringContainsString('dashboard-topbar__nav', $html);
        $this->assertStringContainsString('href="/user/dashboard#service-status"', $html);
        $this->assertStringContainsString('customer-booking-grid', $html);
        $this->assertStringContainsString('name="plat_nomor"', $html);
        $this->assertStringContainsString('name="tanggal_booking"', $html);
        $this->assertStringContainsString('Pesanan saya', $html);
        $this->assertStringContainsString('Keluar', $html);
    }

    public function test_staff_dashboard_uses_the_bengkel_syifa_panel_navigation(): void
    {
        $admin = User::factory()->make(['role' => User::ROLE_ADMIN]);
        $this->actingAs($admin);
        $this->withViewErrors([]);

        $html = view('dashboard.mekanik', ['bookings' => collect()])->render();

        $this->assertStringContainsString('dashboard-topbar__nav', $html);
        $this->assertStringNotContainsString('workshop-sidebar', $html);
        $this->assertStringContainsString('workshop-panel.css', $html);
        $this->assertStringContainsString('Kelola akun', $html);
        $this->assertStringNotContainsString('Data pelanggan', $html);
        $this->assertStringContainsString('Keluar', $html);
        $this->assertStringNotContainsString('Alur servis', $html);
        $this->assertStringNotContainsString('role-process__card', $html);
    }

    public function test_owner_dashboard_does_not_show_service_flow_cards(): void
    {
        $owner = User::factory()->make(['role' => User::ROLE_OWNER]);
        $this->actingAs($owner);

        $emptyServiceCounts = array_fill_keys(array_keys(Booking::LAYANAN), 0);
        $bookingStats = [
            'daily' => ['label' => 'Hari Ini', 'total' => 0, 'services' => $emptyServiceCounts],
            'monthly' => ['label' => 'Bulan Ini', 'total' => 0, 'services' => $emptyServiceCounts],
            'yearly' => ['label' => 'Tahun Ini', 'total' => 0, 'services' => $emptyServiceCounts],
        ];
        $html = view('dashboard.owner', [
            'bookingStats' => $bookingStats,
            'serviceTypes' => Booking::LAYANAN,
        ])->render();

        $this->assertStringNotContainsString('Alur servis', $html);
        $this->assertStringNotContainsString('role-process__card', $html);
    }

    public function test_owner_and_mechanic_navigation_includes_logout(): void
    {
        $mechanic = User::factory()->make(['role' => User::ROLE_MEKANIK]);
        $this->actingAs($mechanic);
        $mechanicNavigation = view('layout.dashboard-navigation')->render();

        $this->assertStringContainsString('Pekerjaan', $mechanicNavigation);
        $this->assertStringContainsString('Keluar', $mechanicNavigation);

        $owner = User::factory()->make(['role' => User::ROLE_OWNER]);
        $this->actingAs($owner);
        $ownerNavigation = view('layout.dashboard-navigation')->render();

        $this->assertStringContainsString('Data pelanggan', $ownerNavigation);
        $this->assertStringContainsString('Keluar', $ownerNavigation);
    }

    public function test_customer_list_displays_user_accounts(): void
    {
        $owner = User::factory()->make(['role' => User::ROLE_OWNER]);
        $this->actingAs($owner);

        $customer = User::factory()->make([
            'name' => 'Asep Pelanggan',
            'email' => 'asep@example.test',
            'role' => User::ROLE_USER,
            'created_at' => now()->setDate(2026, 11, 28),
        ]);
        $html = view('admin.pelanggan.index', [
            'customers' => collect([$customer]),
        ])->render();

        $this->assertStringContainsString('Asep Pelanggan', $html);
        $this->assertStringContainsString('asep@example.test', $html);
        $this->assertStringContainsString('28 Nov 2026', $html);
        $this->assertStringNotContainsString('Tambah Pelanggan', $html);
    }

    public function test_mechanic_can_select_each_of_the_four_service_stages(): void
    {
        $mechanic = User::factory()->make(['role' => User::ROLE_MEKANIK]);
        $customer = User::factory()->make(['role' => User::ROLE_USER]);
        $this->actingAs($mechanic);
        $this->withViewErrors([]);

        $booking = Booking::make([
            'plat_nomor' => 'B 1842 NVT',
            'jenis_motor' => 'Motor',
            'jenis_layanan' => 'ganti_oli',
            'tanggal_booking' => '2026-11-27',
            'jam_booking' => '09:00:00',
            'status' => 'pending',
        ]);
        $booking->setAttribute('id', 1);
        $booking->setRelation('user', $customer);
        $booking->setRelation('mekanik', null);

        $html = view('dashboard.mekanik', ['bookings' => collect([$booking])])->render();

        $this->assertStringNotContainsString('role-process__card', $html);
        $this->assertStringContainsString('value="pending"', $html);
        $this->assertStringContainsString('value="diagnosed"', $html);
        $this->assertStringContainsString('value="on_progress"', $html);
        $this->assertStringContainsString('value="finished"', $html);
        $this->assertStringContainsString('>Booking</option>', $html);
        $this->assertStringContainsString('>Diagnosa</option>', $html);
        $this->assertStringContainsString('>Pengerjaan</option>', $html);
        $this->assertStringContainsString('>Serah terima</option>', $html);
        $this->assertStringContainsString('name="total_biaya"', $html);
        $this->assertStringContainsString('wajib saat Serah terima', $html);
    }

    public function test_role_dashboard_requires_authentication_and_matching_role(): void
    {
        $this->get('/admin/dashboard')->assertRedirect(route('login'));

        $admin = User::factory()->make(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)->get('/owner/dashboard')->assertForbidden();
    }
}
