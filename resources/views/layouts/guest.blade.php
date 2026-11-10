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
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
            <div>
                <a href="/">
                    <x-application-logo class="w-20 h-20 fill-current text-gray-500" />
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
                {{ $slot }}
            </div>
        </div>
    </body>
</html> --}}


<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Login') - SIKABAR</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>

<body class="min-h-screen bg-slate-50">

    <div class="flex min-h-screen">

        <!-- Left Illustration -->
        <div
            class="relative hidden w-1/2 flex-col justify-between
                    overflow-hidden bg-gradient-to-br from-blue-700
                    via-blue-600 to-sky-500 p-12 text-white lg:flex">

            <div class="absolute -right-24 -top-24 h-80 w-80
                        rounded-full bg-white/10"></div>

            <div class="absolute -bottom-32 -left-20 h-96 w-96
                        rounded-full bg-white/10"></div>

            <div class="relative z-10">
                <h1 class="text-3xl font-bold tracking-wider">
                    SIKABAR
                </h1>
                <p class="mt-1 text-sm text-blue-100">
                    Puskesmas Sunyaragi
                </p>
            </div>

            <div class="relative z-10 max-w-lg">
                <div
                    class="mb-7 flex h-20 w-20 items-center justify-center
                            rounded-3xl bg-white/15 backdrop-blur">
                    <svg class="h-11 w-11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M8 7h8m-8 4h8m-8 4h4m-7 6h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                </div>

                <h2 class="text-4xl font-semibold leading-tight">
                    Kelola Kenaikan Gaji Berkala Lebih Mudah
                </h2>

                <p class="mt-5 leading-relaxed text-blue-100">
                    Sistem informasi untuk membantu pengelolaan,
                    pemantauan, dan pengingat jadwal kenaikan
                    gaji berkala pegawai secara terintegrasi.
                </p>
            </div>

            <p class="relative z-10 text-xs text-blue-100">
                © {{ date('Y') }} SIKABAR - Puskesmas Sunyaragi
            </p>
        </div>

        <!-- Login Form -->
        <div class="flex w-full items-center justify-center px-6 py-12 lg:w-1/2">

            <div class="w-full max-w-md">

                <div class="mb-10 text-center lg:hidden">
                    <h1 class="text-3xl font-bold text-blue-700">
                        SIKABAR
                    </h1>
                    <p class="mt-1 text-sm text-slate-400">
                        Puskesmas Sunyaragi
                    </p>
                </div>

                @yield('content')

            </div>
        </div>
    </div>

</body>

</html>
