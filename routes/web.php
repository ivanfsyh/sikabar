<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PegawaiController;
use App\Http\Controllers\PegawaiImportController;
use App\Http\Controllers\RiwayatKgbController;
use App\Http\Controllers\AturanPengingatController;
use App\Http\Controllers\PengaturanController;
use App\Http\Controllers\RiwayatPengingatController;
use App\Http\Controllers\TemplatePesanController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', function () {

        if (auth()->user()->hasRole('admin')) {
            return redirect()->route('admin.dashboard');
        }

        if (auth()->user()->hasRole('pimpinan')) {
            return redirect()->route('pimpinan.dashboard');
        }

        abort(403);
    })->middleware('auth')->name('dashboard');

    // Halaman khusus Admin
    Route::middleware(['role:admin'])->group(function () {

        Route::get('/admin/dashboard', [DashboardController::class, 'admin'])->name('admin.dashboard');

        // Manajemen Pegawai (route import harus di atas resource)
        Route::get('pegawai/import', [PegawaiImportController::class, 'index'])->name('pegawai.import');
        Route::get('pegawai/import/template', [PegawaiImportController::class, 'template'])->name('pegawai.import.template');
        Route::post('pegawai/import/preview', [PegawaiImportController::class, 'preview'])->name('pegawai.import.preview');
        Route::post('pegawai/import', [PegawaiImportController::class, 'store'])->name('pegawai.import.store');
        Route::resource('pegawai', PegawaiController::class)->parameters(['pegawai' => 'pegawai']);

        // Riwayat KGB
        Route::get('kgb', [RiwayatKgbController::class, 'index'])->name('kgb.index');
        Route::post('pegawai/{pegawai}/kgb', [RiwayatKgbController::class, 'store'])->name('kgb.store');
        Route::put('pegawai/{pegawai}/kgb/{kgb}', [RiwayatKgbController::class, 'update'])->name('kgb.update');
        Route::delete('pegawai/{pegawai}/kgb/{kgb}', [RiwayatKgbController::class, 'destroy'])->name('kgb.destroy');

        // ===== Sistem Pengingat =====
        Route::prefix('pengingat')->name('pengingat.')->group(function () {
            Route::get('aturan', [AturanPengingatController::class, 'index'])->name('aturan.index');
            Route::post('aturan', [AturanPengingatController::class, 'store'])->name('aturan.store');
            Route::put('aturan/{aturan}', [AturanPengingatController::class, 'update'])->name('aturan.update');
            Route::patch('aturan/{aturan}/toggle', [AturanPengingatController::class, 'toggle'])->name('aturan.toggle');
            Route::delete('aturan/{aturan}', [AturanPengingatController::class, 'destroy'])->name('aturan.destroy');

            Route::get('template', [TemplatePesanController::class, 'index'])->name('template.index');
            Route::post('template', [TemplatePesanController::class, 'store'])->name('template.store');
            Route::put('template/{template}', [TemplatePesanController::class, 'update'])->name('template.update');
            Route::delete('template/{template}', [TemplatePesanController::class, 'destroy'])->name('template.destroy');

            Route::get('riwayat', [RiwayatPengingatController::class, 'index'])->name('riwayat.index');
            Route::post('riwayat/{riwayat}/kirim-ulang', [RiwayatPengingatController::class, 'kirimUlang'])->name('riwayat.kirim-ulang');
        });

        // ===== Pengaturan Integrasi (KirimDev, Email, Jadwal) =====
        Route::get('pengaturan/integrasi', [PengaturanController::class, 'integrasi'])->name('pengaturan.integrasi');
        Route::put('pengaturan/integrasi', [PengaturanController::class, 'simpan'])->name('pengaturan.integrasi.simpan');
        Route::post('pengaturan/kirim/tes', [PengaturanController::class, 'tesKoneksi'])->name('pengaturan.kirim.tes');
        Route::post('pengaturan/kirim/uji', [PengaturanController::class, 'ujiKirim'])->name('pengaturan.kirim.uji');
        Route::post('pengaturan/email/uji', [PengaturanController::class, 'ujiEmail'])->name('pengaturan.email.uji');
    });

    // Halaman khusus Pimpinan
    Route::middleware(['role:pimpinan'])->group(function () {

        Route::get('/pimpinan/dashboard', [DashboardController::class, 'pimpinan'])->name('pimpinan.dashboard');
    });
});

require __DIR__ . '/auth.php';
