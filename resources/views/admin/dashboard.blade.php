@extends('layouts.app')

@section('title', 'Dashboard Admin')
@section('page-title', 'Dashboard Admin')

@section('content')

    <div class="mb-8">
        <h1 class="text-2xl font-bold text-slate-800">
            Selamat Datang, {{ auth()->user()->name }}
        </h1>

        <p class="mt-2 text-sm text-slate-400">
            Berikut ringkasan pengelolaan Kenaikan Gaji Berkala.
        </p>
    </div>

    <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">

        @foreach ([['Total Pegawai', '0', 'users'], ['KGB Bulan Ini', '0', 'calendar'], ['KGB Mendatang', '0', 'clock'], ['Notifikasi Terkirim', '0', 'send']] as $item)
            <div class="rounded-2xl border border-slate-100
                    bg-white p-6 shadow-sm">

                <div
                    class="mb-5 flex h-11 w-11 items-center
                        justify-center rounded-xl bg-blue-50
                        text-blue-600">

                    @if ($item[2] === 'users')
                        <span class="text-xl">♙</span>
                    @elseif($item[2] === 'calendar')
                        <span class="text-xl">▦</span>
                    @elseif($item[2] === 'clock')
                        <span class="text-xl">◷</span>
                    @else
                        <span class="text-xl">➤</span>
                    @endif

                </div>

                <p class="text-sm text-slate-400">{{ $item[0] }}</p>

                <h3 class="mt-2 text-3xl font-bold text-slate-800">
                    {{ $item[1] }}
                </h3>

            </div>
        @endforeach

    </div>

    <div class="mt-8 rounded-2xl border border-slate-100
            bg-white p-6 shadow-sm">

        <h3 class="font-semibold text-slate-800">
            Informasi Sistem
        </h3>

        <p class="mt-2 text-sm leading-relaxed text-slate-500">
            Selamat menggunakan SIKABAR. Sistem ini digunakan
            untuk membantu pengelolaan data pegawai dan monitoring
            jadwal kenaikan gaji berkala di Puskesmas Sunyaragi.
        </p>

    </div>

@endsection
