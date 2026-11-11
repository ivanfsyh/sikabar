<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PegawaiController;
use App\Http\Controllers\PegawaiImportController;
use App\Http\Controllers\RiwayatKgbController;
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
    });

    // Halaman khusus Pimpinan
    Route::middleware(['role:pimpinan'])->group(function () {

        Route::get('/pimpinan/dashboard', [DashboardController::class, 'pimpinan'])->name('pimpinan.dashboard');
    });
});

require __DIR__ . '/auth.php';
