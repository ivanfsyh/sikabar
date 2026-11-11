@extends('layouts.app')

@section('title', 'Ubah Pegawai')
@section('page-title', 'Ubah Pegawai')

@section('content')
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Ubah Data Pegawai</h1>
            <p class="mt-1 text-sm text-slate-400">{{ $pegawai->nama }}</p>
        </div>
        <a href="{{ route('pegawai.show', $pegawai) }}" class="text-sm text-slate-500 hover:text-blue-700">&larr; Kembali</a>
    </div>

    <form method="POST" action="{{ route('pegawai.update', $pegawai) }}"
        class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
        @csrf
        @method('PUT')
        @include('pegawai._form')

        <div class="mt-8 flex justify-end gap-3">
            <a href="{{ route('pegawai.show', $pegawai) }}"
                class="rounded-xl px-5 py-2.5 text-sm text-slate-500 hover:bg-slate-50">Batal</a>
            <button type="submit"
                class="rounded-xl bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">Simpan
                Perubahan</button>
        </div>
    </form>
@endsection
