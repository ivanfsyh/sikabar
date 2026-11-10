<?php

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

        Route::get('/admin/dashboard', function () {
            return view('admin.dashboard');
        })->name('admin.dashboard');
    });

    // Halaman khusus Pimpinan
    Route::middleware(['role:pimpinan'])->group(function () {

        Route::get('/pimpinan/dashboard', function () {
            return view('pimpinan.dashboard');
        })->name('pimpinan.dashboard');
    });
});

require __DIR__ . '/auth.php';
