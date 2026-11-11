<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('mekanik_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('plat_nomor', 15);
            $table->string('jenis_motor', 100);
            $table->string('jenis_layanan', 50);
            $table->text('keluhan')->nullable();
            $table->date('tanggal_booking');
            $table->time('jam_booking');
            $table->string('status', 20)->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
