@extends('layouts.app')

@section('title', 'Laporan KGB')
@section('page-title', 'Laporan KGB')

@section('content')

    @php
        $rupiah = fn($n) => $n === null ? '-' : 'Rp ' . number_format($n, 0, ',', '.');
        $badge = [
            'terlewat' => 'bg-red-50 text-red-600',
            'bulan_ini' => 'bg-blue-50 text-blue-600',
            'mendatang' => 'bg-amber-50 text-amber-600',
            'belum_ada' => 'bg-slate-100 text-slate-500',
        ];
        $queryFilter = request()->except('page');
    @endphp

    {{-- Judul + tombol aksi --}}
    <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Laporan KGB</h1>
            <p class="mt-1 text-sm text-slate-400">Rekap jadwal Kenaikan Gaji Berkala pegawai aktif.</p>
        </div>

        <div class="flex gap-2">
            <a href="{{ route('pimpinan.laporan.cetak', $queryFilter) }}" target="_blank"
                class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">
                Cetak / PDF
            </a>
            <a href="{{ route('pimpinan.laporan.ekspor', $queryFilter) }}"
                class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">
                Ekspor Excel
            </a>
        </div>
    </div>

    {{-- Filter --}}
    <form method="GET" action="{{ route('pimpinan.laporan') }}"
        class="mb-6 grid gap-4 rounded-2xl border border-slate-100 bg-white p-5 shadow-sm sm:grid-cols-2 lg:grid-cols-6">

        <div class="lg:col-span-2">
            <label class="mb-1 block text-xs font-medium text-slate-500">Cari NIP / Nama</label>
            <input type="text" name="q" value="{{ $filter['q'] ?? '' }}" placeholder="Ketik NIP atau nama..."
                class="w-full rounded-xl border-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500">
        </div>

        <div>
            <label class="mb-1 block text-xs font-medium text-slate-500">Unit Kerja</label>
            <select name="unit"
                class="w-full rounded-xl border-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="">Semua unit</option>
                @foreach ($unitTersedia as $u)
                    <option value="{{ $u }}" @selected(($filter['unit'] ?? '') === $u)>{{ $u }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="mb-1 block text-xs font-medium text-slate-500">Status</label>
            <select name="status"
                class="w-full rounded-xl border-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="">Semua status</option>
                @foreach ($statusList as $kode => $label)
                    <option value="{{ $kode }}" @selected(($filter['status'] ?? '') === $kode)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="mb-1 block text-xs font-medium text-slate-500">KGB berikutnya dari</label>
            <input type="date" name="dari" value="{{ $filter['dari'] ?? '' }}"
                class="w-full rounded-xl border-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500">
        </div>

        <div>
            <label class="mb-1 block text-xs font-medium text-slate-500">Sampai</label>
            <input type="date" name="sampai" value="{{ $filter['sampai'] ?? '' }}"
                class="w-full rounded-xl border-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500">
            @error('sampai')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-end gap-2 sm:col-span-2 lg:col-span-6">
            <button type="submit"
                class="rounded-xl bg-blue-600 px-5 py-2 text-sm font-medium text-white hover:bg-blue-700">Terapkan</button>
            <a href="{{ route('pimpinan.laporan') }}"
                class="rounded-xl border border-slate-200 px-5 py-2 text-sm text-slate-600 hover:bg-slate-50">Reset</a>
        </div>
    </form>

    {{-- Kartu ringkasan --}}
    <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-5">
        @foreach ([['Total (sesuai filter)', $ringkasan['total'], 'text-slate-800'], ['Terlewat', $ringkasan['terlewat'], 'text-red-600'], ['Bulan Ini', $ringkasan['bulan_ini'], 'text-blue-600'], ['Mendatang', $ringkasan['mendatang'], 'text-amber-600'], ['Belum Ada Data KGB', $ringkasan['belum_ada'], 'text-slate-500']] as [$judul, $nilai, $warna])
            <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
                <p class="text-sm text-slate-400">{{ $judul }}</p>
                <h3 class="mt-2 text-3xl font-bold {{ $warna }}">{{ $nilai }}</h3>
            </div>
        @endforeach
    </div>

    <div class="mt-5 rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
        <p class="text-sm text-slate-400">Estimasi tambahan gaji pokok per bulan</p>
        <h3 class="mt-1 text-2xl font-bold text-slate-800">{{ $rupiah($ringkasan['estimasi_kenaikan']) }}</h3>
        <p class="mt-1 text-xs text-slate-400">
            Perkiraan kasar untuk pegawai di tabel di bawah (kecuali yang belum ada data KGB), dengan asumsi kenaikannya
            sama seperti KGB terakhir masing-masing. Bukan angka resmi.
        </p>
    </div>

    {{-- Tabel utama --}}
    <div class="mt-8 rounded-2xl border border-slate-100 bg-white shadow-sm">
        <div class="border-b border-slate-100 p-5">
            <h3 class="font-semibold text-slate-800">Daftar Pegawai &amp; Jadwal KGB</h3>
            <p class="mt-1 text-xs text-slate-400">Diurutkan dari jadwal KGB terdekat.</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-400">
                    <tr>
                        <th class="px-5 py-3 font-semibold">No</th>
                        <th class="px-5 py-3 font-semibold">Pegawai</th>
                        <th class="px-5 py-3 font-semibold">Unit / Jabatan</th>
                        <th class="px-5 py-3 font-semibold">Gol.</th>
                        <th class="px-5 py-3 font-semibold">KGB Terakhir</th>
                        <th class="px-5 py-3 font-semibold">KGB Berikutnya</th>
                        <th class="px-5 py-3 font-semibold">Status</th>
                        <th class="px-5 py-3 text-right font-semibold">Gaji Pokok</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse ($baris as $r)
                        @php
                            $p = $r['pegawai'];
                            $s = $r['sisa_hari'];
                        @endphp
                        <tr class="hover:bg-slate-50/60">
                            <td class="px-5 py-4 text-slate-400">
                                {{ ($baris->currentPage() - 1) * $baris->perPage() + $loop->iteration }}
                            </td>
                            <td class="px-5 py-4">
                                <span class="font-semibold text-slate-800">{{ $p->nama }}</span>
                                <p class="mt-0.5 text-xs text-slate-400">{{ $p->nip }}</p>
                            </td>
                            <td class="px-5 py-4 text-slate-600">
                                {{ $p->unit_kerja ?: '-' }}
                                <p class="mt-0.5 text-xs text-slate-400">{{ $p->jabatan ?: '-' }}</p>
                            </td>
                            <td class="px-5 py-4 text-slate-600">{{ $p->pangkat_golongan ?: '-' }}</td>
                            <td class="px-5 py-4 text-slate-600">
                                {{ $r['tmt_terakhir']?->translatedFormat('d M Y') ?? '-' }}
                            </td>
                            <td class="px-5 py-4 text-slate-600">
                                {{ $r['tanggal']?->translatedFormat('d M Y') ?? '-' }}
                                @if ($s !== null)
                                    <p class="mt-0.5 text-xs text-slate-400">
                                        @if ($s < 0)
                                            Terlambat {{ abs($s) }} hari
                                        @elseif ($s === 0)
                                            Hari ini
                                        @else
                                            {{ $s }} hari lagi
                                        @endif
                                    </p>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <span
                                    class="whitespace-nowrap rounded-full px-2.5 py-1 text-xs font-medium {{ $badge[$r['status']] }}">
                                    {{ $statusList[$r['status']] }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-right text-slate-600">{{ $rupiah($r['gaji_sekarang']) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-12 text-center text-sm text-slate-400">
                                Tidak ada data yang sesuai dengan filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($baris->hasPages())
            <div class="border-t border-slate-100 p-4">{{ $baris->links() }}</div>
        @endif
    </div>

    <div class="mt-8 grid gap-6 xl:grid-cols-3">

        {{-- Ringkasan per unit --}}
        <div class="rounded-2xl border border-slate-100 bg-white shadow-sm xl:col-span-2">
            <div class="border-b border-slate-100 p-5">
                <h3 class="font-semibold text-slate-800">Ringkasan per Unit Kerja</h3>
                <p class="mt-1 text-xs text-slate-400">Diurutkan dari unit dengan KGB terlewat terbanyak.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-400">
                        <tr>
                            <th class="px-5 py-3 font-semibold">Unit</th>
                            <th class="px-5 py-3 text-center font-semibold">Total</th>
                            <th class="px-5 py-3 text-center font-semibold">Terlewat</th>
                            <th class="px-5 py-3 text-center font-semibold">Bulan Ini</th>
                            <th class="px-5 py-3 text-center font-semibold">Mendatang</th>
                            <th class="px-5 py-3 text-center font-semibold">Belum Ada</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse ($perUnit as $u)
                            <tr>
                                <td class="px-5 py-3 font-medium text-slate-700">{{ $u['unit'] }}</td>
                                <td class="px-5 py-3 text-center">{{ $u['total'] }}</td>
                                <td
                                    class="px-5 py-3 text-center {{ $u['terlewat'] ? 'font-semibold text-red-600' : 'text-slate-400' }}">
                                    {{ $u['terlewat'] }}</td>
                                <td class="px-5 py-3 text-center">{{ $u['bulan_ini'] }}</td>
                                <td class="px-5 py-3 text-center">{{ $u['mendatang'] }}</td>
                                <td class="px-5 py-3 text-center">{{ $u['belum_ada'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-8 text-center text-sm text-slate-400">Belum ada data.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Status pengingat --}}
        <div class="rounded-2xl border border-slate-100 bg-white shadow-sm">
            <div class="border-b border-slate-100 p-5">
                <h3 class="font-semibold text-slate-800">Status Pengingat</h3>
                <p class="mt-1 text-xs text-slate-400">30 hari terakhir.</p>
            </div>
            <ul class="divide-y divide-slate-50 text-sm">
                @foreach ([['terkirim', 'Terkirim', 'text-emerald-600'], ['menunggu', 'Menunggu', 'text-amber-600'], ['gagal', 'Gagal', 'text-red-600'], ['dilewati', 'Dilewati', 'text-slate-500']] as [$kode, $label, $warna])
                    <li class="flex items-center justify-between px-5 py-3">
                        <span class="text-slate-600">{{ $label }}</span>
                        <span
                            class="font-semibold {{ $warna }}">{{ $pengingat[$kode] + ($kode === 'menunggu' ? $pengingat['diproses'] : 0) }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

@endsection
