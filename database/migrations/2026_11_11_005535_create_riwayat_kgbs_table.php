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
        Schema::create('riwayat_kgbs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pegawai_id')->constrained('pegawais')->cascadeOnDelete();
            $table->string('no_sk');
            $table->date('tanggal_sk');
            $table->string('pejabat_penandatangan')->nullable();
            $table->date('tmt_kgb');
            $table->string('golongan_ruang', 100)->nullable();
            $table->unsignedTinyInteger('masa_kerja_tahun')->default(0);
            $table->unsignedTinyInteger('masa_kerja_bulan')->default(0);
            $table->unsignedBigInteger('gaji_pokok_lama')->default(0);
            $table->unsignedBigInteger('gaji_pokok_baru');
            $table->date('tmt_kgb_berikutnya');
            $table->string('file_sk')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->index(['pegawai_id', 'tmt_kgb']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('riwayat_kgbs');
    }
};
