@extends('layouts.guest')

@section('title', 'Login')

@section('content')

    <div class="mb-8">
        <span class="rounded-full bg-blue-50 px-4 py-2
                 text-xs font-semibold text-blue-600">
            Selamat Datang
        </span>

        <h2 class="mt-5 text-3xl font-bold text-slate-800">
            Login SIKABAR
        </h2>

        <p class="mt-2 text-sm text-slate-400">
            Silakan masuk untuk melanjutkan ke aplikasi.
        </p>
    </div>

    @if ($errors->any())
        <div class="mb-5 rounded-xl border border-red-100
                bg-red-50 p-4 text-sm text-red-600">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <div>
            <label class="mb-2 block text-sm font-medium text-slate-600">
                Email
            </label>

            <input type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                placeholder="Masukkan email"
                class="w-full rounded-xl border border-slate-200
                      bg-slate-50 px-4 py-3 text-sm outline-none
                      transition focus:border-blue-500
                      focus:bg-white focus:ring-4 focus:ring-blue-50">
        </div>

        <div>
            <label class="mb-2 block text-sm font-medium text-slate-600">
                Password
            </label>

            <input type="password" name="password" required autocomplete="current-password" placeholder="Masukkan password"
                class="w-full rounded-xl border border-slate-200
                      bg-slate-50 px-4 py-3 text-sm outline-none
                      transition focus:border-blue-500
                      focus:bg-white focus:ring-4 focus:ring-blue-50">
        </div>

        <div class="flex items-center justify-between">
            <label class="flex items-center gap-2 text-xs text-slate-500">
                <input type="checkbox" name="remember" class="rounded border-slate-300 text-blue-600">
                Ingat saya
            </label>

            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="text-xs font-medium text-blue-600 hover:text-blue-800">
                    Lupa password?
                </a>
            @endif
        </div>

        <button type="submit"
            class="w-full rounded-xl bg-blue-600 py-3.5
                   text-sm font-semibold text-white shadow-lg
                   shadow-blue-200 transition hover:bg-blue-700
                   active:scale-[0.99]">
            Masuk ke Aplikasi
        </button>
    </form>

    <p class="mt-8 text-center text-xs text-slate-400">
        © {{ date('Y') }} SIKABAR<br>
        Puskesmas Sunyaragi
    </p>

@endsection
