<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Pengaturan (key-value): kunci API, email, jadwal
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // Template pesan: isi email + pemetaan template WhatsApp (Meta/KirimDev)
        Schema::create('template_pesans', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('subjek');
            $table->text('isi');
            $table->string('wa_template', 100)->nullable();   // nama template di KirimDev
            $table->string('wa_bahasa', 10)->default('id');
            $table->json('wa_params')->nullable();            // daftar [{nama, nilai}]
            $table->timestamps();
        });

        // Aturan: kirim berapa hari sebelum KGB, memakai template apa
        Schema::create('aturan_pengingats', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            // >0 = hari sebelum jatuh tempo, 0 = hari H, <0 = hari setelah jatuh tempo
            $table->smallInteger('hari_sebelum');
            $table->foreignId('template_pesan_id')->constrained('template_pesans')->restrictOnDelete();
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });

        // Riwayat pengiriman
        Schema::create('riwayat_pengingats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pegawai_id')->constrained('pegawais')->cascadeOnDelete();
            $table->foreignId('aturan_pengingat_id')->nullable()->constrained('aturan_pengingats')->nullOnDelete();
            $table->string('nama_aturan')->nullable();
            $table->date('tmt_jatuh_tempo');
            $table->string('kanal', 10)->nullable();            // wa | email
            $table->string('status', 15)->default('menunggu');  // menunggu, diproses, terkirim, gagal, dilewati
            $table->string('subjek')->nullable();
            $table->text('pesan');
            $table->string('wa_template', 100)->nullable();
            $table->string('wa_bahasa', 10)->nullable();
            $table->json('wa_params')->nullable();              // nilai parameter yang sudah terisi
            $table->string('idempotency_key', 64)->nullable();
            $table->string('tujuan')->nullable();
            $table->unsignedTinyInteger('percobaan')->default(0);
            $table->text('respon')->nullable();
            $table->text('error')->nullable();
            $table->timestamp('terkirim_at')->nullable();
            $table->timestamps();

            // Kunci anti-duplikat: satu pengingat per pegawai + aturan + tanggal jatuh tempo
            $table->unique(['pegawai_id', 'aturan_pengingat_id', 'tmt_jatuh_tempo'], 'uniq_pengingat');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('riwayat_pengingats');
        Schema::dropIfExists('aturan_pengingats');
        Schema::dropIfExists('template_pesans');
        Schema::dropIfExists('settings');
    }
};
