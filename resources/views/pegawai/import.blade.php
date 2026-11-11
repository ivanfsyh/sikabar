@extends('layouts.app')

@section('title', 'Import Pegawai')
@section('page-title', 'Import Pegawai')

@section('content')
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Import Data Pegawai</h1>
            <p class="mt-1 text-sm text-slate-400">Unggah file Excel, periksa hasil validasi, lalu simpan.</p>
        </div>
        <a href="{{ route('pegawai.index') }}" class="text-sm text-slate-500 hover:text-blue-700">&larr; Kembali</a>
    </div>

    <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h3 class="font-semibold text-slate-800">1. Siapkan file</h3>
                <p class="mt-1 text-sm text-slate-500">
                    Gunakan template agar kolom sesuai. Kolom <b>NIP</b> harus berformat <b>Teks</b>.
                    Tanggal gunakan format <code>YYYY-MM-DD</code>; jenis kelamin <code>L</code>/<code>P</code>.
                </p>
            </div>
            <a href="{{ route('pegawai.import.template') }}"
                class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-50">Unduh
                Template</a>
        </div>

        <form method="POST" action="{{ route('pegawai.import.preview') }}" enctype="multipart/form-data"
            class="mt-6 flex flex-wrap items-end gap-3 border-t border-slate-100 pt-6">
            @csrf
            <div class="grow sm:max-w-md">
                <h3 class="mb-2 font-semibold text-slate-800">2. Unggah file</h3>
                <input type="file" name="file" accept=".xlsx,.xls,.csv" required
                    class="w-full rounded-xl border border-slate-200 text-sm file:mr-3 file:rounded-l-xl file:border-0 file:bg-blue-50 file:px-4 file:py-2.5 file:text-blue-700">
                @error('file')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>
            <button class="rounded-xl bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">Periksa
                File</button>
        </form>
    </div>

    @isset($valid)
        <div class="mt-6 grid gap-5 sm:grid-cols-2">
            <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
                <p class="text-sm text-slate-400">Baris valid</p>
                <h3 class="mt-2 text-3xl font-bold text-emerald-600">{{ count($valid) }}</h3>
            </div>
            <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
                <p class="text-sm text-slate-400">Baris bermasalah</p>
                <h3 class="mt-2 text-3xl font-bold text-red-500">{{ count($invalid) }}</h3>
            </div>
        </div>

        @if (count($invalid))
            <div class="mt-6 rounded-2xl border border-slate-100 bg-white shadow-sm">
                <div class="border-b border-slate-100 p-5">
                    <h3 class="font-semibold text-slate-800">Baris bermasalah (tidak akan disimpan)</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-400">
                            <tr>
                                <th class="px-5 py-3">Baris</th>
                                <th class="px-5 py-3">NIP</th>
                                <th class="px-5 py-3">Nama</th>
                                <th class="px-5 py-3">Masalah</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @foreach ($invalid as $row)
                                <tr>
                                    <td class="px-5 py-3 text-slate-500">{{ $row['line'] }}</td>
                                    <td class="px-5 py-3 text-slate-600">{{ $row['data']['nip'] }}</td>
                                    <td class="px-5 py-3 text-slate-600">{{ $row['data']['nama'] ?: '-' }}</td>
                                    <td class="px-5 py-3 text-red-500">
                                        <ul class="list-inside list-disc">
                                            @foreach ($row['errors'] as $err)
                                                <li>{{ $err }}</li>
                                            @endforeach
                                        </ul>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        @if (count($valid))
            <div class="mt-6 rounded-2xl border border-slate-100 bg-white shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 p-5">
                    <h3 class="font-semibold text-slate-800">3. Pratinjau data valid</h3>
                    <form method="POST" action="{{ route('pegawai.import.store') }}">
                        @csrf
                        <button
                            class="rounded-xl bg-emerald-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">
                            Simpan {{ count($valid) }} Pegawai
                        </button>
                    </form>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-400">
                            <tr>
                                <th class="px-5 py-3">NIP</th>
                                <th class="px-5 py-3">Nama</th>
                                <th class="px-5 py-3">Gol.</th>
                                <th class="px-5 py-3">Jabatan</th>
                                <th class="px-5 py-3">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @foreach (array_slice($valid, 0, 50) as $row)
                                <tr>
                                    <td class="px-5 py-3 text-slate-600">{{ $row['nip'] }}</td>
                                    <td class="px-5 py-3 font-medium text-slate-700">{{ $row['nama'] }}</td>
                                    <td class="px-5 py-3 text-slate-600">{{ $row['pangkat_golongan'] ?? '-' }}</td>
                                    <td class="px-5 py-3 text-slate-600">{{ $row['jabatan'] ?? '-' }}</td>
                                    <td class="px-5 py-3 capitalize text-slate-600">{{ $row['status'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if (count($valid) > 50)
                    <p class="border-t border-slate-100 p-4 text-center text-xs text-slate-400">Menampilkan 50 dari
                        {{ count($valid) }} baris.</p>
                @endif
            </div>
        @endif
    @endisset
@endsection
