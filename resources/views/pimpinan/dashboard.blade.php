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

    <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">

        @foreach ([['Total Pegawai', $totalPegawai, '♙', 'bg-blue-50 text-blue-600', $pegawaiAktif . ' pegawai aktif'], ['KGB Bulan Ini', $kgbBulanIni, '✓', 'bg-emerald-50 text-emerald-600', $awalBulan->translatedFormat('F Y')], ['KGB Mendatang', $kgbMendatang, '◷', 'bg-amber-50 text-amber-600', '3 bulan ke depan'], ['KGB Terlewat', $kgbTerlewat, '!', 'bg-red-50 text-red-600', 'perlu ditindaklanjuti']] as [$title, $value, $icon, $color, $note])
            <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
                <div class="mb-5 flex h-11 w-11 items-center justify-center rounded-xl {{ $color }}">
                    <span class="text-xl font-semibold">{{ $icon }}</span>
                </div>
                <p class="text-sm text-slate-400">{{ $title }}</p>
                <h3 class="mt-2 text-3xl font-bold text-slate-800">{{ $value }}</h3>
                <p class="mt-1 text-xs text-slate-400">{{ $note }}</p>
            </div>
        @endforeach

    </div>

    @include('partials.jadwal-kgb', ['detail' => false])

@endsection
