@extends('layouts.app')

@section('title', 'Dashboard Pimpinan')
@section('page-title', 'Monitoring KGB')

@section('content')

    <div class="mb-8">
        <h1 class="text-2xl font-bold text-slate-800">
            Selamat Datang, {{ auth()->user()->name }}
        </h1>

        <p class="mt-2 text-sm text-slate-400">
            Ringkasan monitoring kenaikan gaji berkala pegawai.
        </p>
    </div>

    <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">

        <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
            <div
                class="mb-5 flex h-11 w-11 items-center justify-center
                    rounded-xl bg-blue-50 text-blue-600">
                <span class="text-xl">♙</span>
            </div>
            <p class="text-sm text-slate-400">Total Pegawai</p>
            <h3 class="mt-2 text-3xl font-bold text-slate-800">0</h3>
        </div>

        <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
            <div
                class="mb-5 flex h-11 w-11 items-center justify-center
                    rounded-xl bg-amber-50 text-amber-600">
                <span class="text-xl">◷</span>
            </div>
            <p class="text-sm text-slate-400">KGB Mendatang</p>
            <h3 class="mt-2 text-3xl font-bold text-slate-800">0</h3>
        </div>

        <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
            <div
                class="mb-5 flex h-11 w-11 items-center justify-center
                    rounded-xl bg-emerald-50 text-emerald-600">
                <span class="text-xl">✓</span>
            </div>
            <p class="text-sm text-slate-400">KGB Bulan Ini</p>
            <h3 class="mt-2 text-3xl font-bold text-slate-800">0</h3>
        </div>

    </div>

    <div class="mt-8 rounded-2xl border border-slate-100
            bg-white p-6 shadow-sm">

        <h3 class="font-semibold text-slate-800">
            Monitoring Kenaikan Gaji Berkala
        </h3>

        <p class="mt-2 text-sm text-slate-500">
            Melalui halaman ini pimpinan dapat memantau jadwal
            kenaikan gaji berkala dan melihat laporan perkembangan
            KGB pegawai Puskesmas Sunyaragi.
        </p>

    </div>

@endsection
