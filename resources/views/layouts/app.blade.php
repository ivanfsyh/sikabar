{{-- <!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>
    </body>
</html> --}}


<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Dashboard') - SIKABAR</title>

    <link rel="icon" type="image/png" href="{{ asset('img/favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>

<body class="bg-slate-50 text-slate-700 antialiased">

    <div x-data="{ sidebarOpen: false }" class="min-h-screen">

        <!-- Overlay Mobile -->
        <div x-show="sidebarOpen" @click="sidebarOpen = false" x-transition.opacity
            class="fixed inset-0 z-40 bg-slate-900/40 lg:hidden" x-cloak>
        </div>

        <!-- Sidebar -->
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
            class="fixed inset-y-0 left-0 z-50 flex w-72 flex-col
               border-r border-slate-100 bg-white transition-transform
               duration-300 lg:translate-x-0">

            <div class="flex h-20 items-center gap-3 border-b border-slate-100 px-6">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center">
                    <img src="{{ asset('img/favicon.png') }}" alt="Logo SIKABAR" class="h-11 w-11 object-contain">
                </div>

                <div>
                    <h1 class="text-xl font-bold tracking-wide text-blue-700">
                        SIKABAR
                    </h1>
                    <p class="text-[10px] text-slate-400">
                        Puskesmas Sunyaragi
                    </p>
                </div>
            </div>

            <div class="flex-1 space-y-2 overflow-y-auto px-4 py-6">

                <p
                    class="px-3 text-[10px] font-semibold uppercase
                      tracking-widest text-slate-400">
                    Menu Utama
                </p>

                <a href="{{ route('dashboard') }}"
                    class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm
      {{ request()->routeIs('dashboard', 'admin.dashboard', 'pimpinan.dashboard') ? 'bg-blue-50 font-semibold text-blue-700' : 'text-slate-600 hover:bg-blue-50 hover:text-blue-700' }}">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M3 10.5L12 3l9 7.5M5 9v11h14V9" />
                    </svg>
                    Dashboard
                </a>

                @role('admin')
                    <p
                        class="px-3 pt-5 text-[10px] font-semibold
                          uppercase tracking-widest text-slate-400">
                        Administrasi
                    </p>

                    <a href="{{ route('pegawai.index') }}"
                        class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm
      {{ request()->routeIs('pegawai.*') ? 'bg-blue-50 font-semibold text-blue-700' : 'text-slate-600 hover:bg-blue-50 hover:text-blue-700' }}">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2m15-13a4 4 0 11-8 0 4 4 0 018 0zm3 13v-2a4 4 0 00-3-3.87" />
                        </svg>
                        Data Pegawai
                    </a>

                    <a href="{{ route('kgb.index') }}"
                        class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm
      {{ request()->routeIs('kgb.*') ? 'bg-blue-50 font-semibold text-blue-700' : 'text-slate-600 hover:bg-blue-50 hover:text-blue-700' }}">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M12 6v6l4 2m5-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Jadwal KGB
                    </a>

                    <p class="mb-1 mt-5 px-4 text-xs font-semibold uppercase tracking-wider text-slate-400">Pengingat</p>

                    @foreach ([['pengingat.aturan.index', 'pengingat.aturan.*', 'Aturan Pengingat'], ['pengingat.template.index', 'pengingat.template.*', 'Template Pesan'], ['pengingat.riwayat.index', 'pengingat.riwayat.*', 'Riwayat Pengiriman'], ['pengaturan.integrasi', 'pengaturan.*', 'Integrasi & Jadwal']] as [$rute, $pola, $teks])
                        <a href="{{ route($rute) }}"
                            class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm
                              {{ request()->routeIs($pola) ? 'bg-blue-50 font-semibold text-blue-700' : 'text-slate-600 hover:bg-blue-50 hover:text-blue-700' }}">
                            <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                            {{ $teks }}
                        </a>
                    @endforeach
                @endrole

                @role('pimpinan')
                    <p
                        class="px-3 pt-5 text-[10px] font-semibold
                          uppercase tracking-widest text-slate-400">
                        Monitoring
                    </p>

                    <a href="{{ route('pimpinan.laporan') }}"
                        class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm
      {{ request()->routeIs('pimpinan.laporan*') ? 'bg-blue-50 font-semibold text-blue-700' : 'text-slate-600 hover:bg-blue-50 hover:text-blue-700' }}">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M3 3v18h18M7 14l4-4 4 4 6-7" />
                        </svg>
                        Laporan KGB
                    </a>
                @endrole

            </div>

            <div class="border-t border-slate-100 p-4">

                <form method="POST" action="{{ route('logout') }}" class="mt-3">
                    @csrf
                    <button type="submit"
                        class="flex w-full items-center gap-3 rounded-xl
                           px-3 py-2 text-sm text-red-500
                           hover:bg-red-50">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M15 12H3m0 0l4-4m-4 4l4 4m4-9V5a2 2 0 012-2h6a2 2 0 012 2v14a2 2 0 01-2 2h-6a2 2 0 01-2-2v-2" />
                        </svg>
                        Keluar Aplikasi
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="min-h-screen lg:pl-72">

            <!-- Header -->
            <header
                class="sticky top-0 z-30 flex h-20 items-center
                       justify-between border-b border-slate-100
                       bg-white/90 px-5 backdrop-blur lg:px-8">

                <div class="flex items-center gap-3">
                    <button @click="sidebarOpen = true" class="rounded-lg p-2 text-slate-600 lg:hidden">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>

                    <div>
                        <h2 class="text-base font-semibold text-slate-800">
                            @yield('page-title', 'Dashboard')
                        </h2>
                        <p class="hidden text-xs text-slate-400 sm:block">
                            Sistem Informasi Kenaikan Gaji Berkala
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <div class="hidden text-right sm:block">
                        <p class="text-sm font-semibold text-slate-700">
                            {{ auth()->user()->name }}
                        </p>
                        <p class="text-xs text-slate-400">
                            Puskesmas Sunyaragi
                        </p>
                    </div>

                    <div
                        class="flex h-10 w-10 items-center justify-center
                            rounded-full bg-blue-100 font-semibold text-blue-700">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                </div>
            </header>

            <main class="p-5 lg:p-8">
                @yield('content')
            </main>

        </div>
    </div>

    <!-- SweetAlert Flash Message -->
    <!-- Konfirmasi hapus (dipakai form dengan atribut data-confirm) -->
    <script>
        document.addEventListener('submit', (e) => {
            const form = e.target;
            if (!form.dataset.confirm || form.dataset.confirmed) return;
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Yakin?',
                text: form.dataset.confirm,
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonText: 'Batal',
                confirmButtonText: 'Ya, hapus'
            }).then((r) => {
                if (r.isConfirmed) {
                    form.dataset.confirmed = '1';
                    form.submit();
                }
            });
        });
    </script>

    @if (session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: @json(session('success')),
                    timer: 2500,
                    showConfirmButton: false
                });
            });
        </script>
    @endif

    @if (session('error'))
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: @json(session('error')),
                    confirmButtonColor: '#4F81C7'
                });
            });
        </script>
    @endif

</body>

</html>
