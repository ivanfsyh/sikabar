@extends('layouts.app')

@section('title', 'Data Pegawai')
@section('page-title', 'Data Pegawai')

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Data Pegawai</h1>
            <p class="mt-1 text-sm text-slate-400">Kelola data pegawai Puskesmas Sunyaragi.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('pegawai.import') }}"
                class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-50">
                Import Excel
            </a>
            <a href="{{ route('pegawai.create') }}"
                class="rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">
                + Tambah Pegawai
            </a>
        </div>
    </div>

    <div class="rounded-2xl border border-slate-100 bg-white shadow-sm">

        <form method="GET" class="flex flex-wrap gap-3 border-b border-slate-100 p-5">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari NIP atau nama..."
                class="w-full rounded-xl border-slate-200 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-blue-500 sm:w-72">

            <select name="status"
                class="rounded-xl border-slate-200 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="">Semua Status</option>
                @foreach (['aktif' => 'Aktif', 'pensiun' => 'Pensiun', 'pindah' => 'Pindah'] as $v => $t)
                    <option value="{{ $v }}" @selected(request('status') === $v)>{{ $t }}</option>
                @endforeach
            </select>

            <select name="unit_kerja"
                class="rounded-xl border-slate-200 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="">Semua Unit Kerja</option>
                @foreach ($unitKerja as $unit)
                    <option value="{{ $unit }}" @selected(request('unit_kerja') === $unit)>{{ $unit }}</option>
                @endforeach
            </select>

            <button
                class="rounded-xl bg-slate-800 px-5 py-2.5 text-sm font-medium text-white hover:bg-slate-700">Cari</button>
            @if (request()->hasAny(['q', 'status', 'unit_kerja']))
                <a href="{{ route('pegawai.index') }}"
                    class="px-3 py-2.5 text-sm text-slate-400 hover:text-slate-600">Reset</a>
            @endif
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-400">
                    <tr>
                        <th class="px-5 py-3 font-semibold">Pegawai</th>
                        <th class="px-5 py-3 font-semibold">Gol.</th>
                        <th class="px-5 py-3 font-semibold">Jabatan</th>
                        <th class="px-5 py-3 font-semibold">KGB Berikutnya</th>
                        <th class="px-5 py-3 font-semibold">Status</th>
                        <th class="px-5 py-3 text-right font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse ($pegawais as $p)
                        @php
                            $kgb = $p->kgbTerbaru;
                            $next = $kgb?->tmt_kgb_berikutnya;
                            $soon = $next && $next->between(now()->startOfDay(), now()->addMonths(3));
                            $late = $next && $next->lt(now()->startOfDay());
                        @endphp
                        <tr class="hover:bg-slate-50/60">
                            <td class="px-5 py-4">
                                <a href="{{ route('pegawai.show', $p) }}"
                                    class="font-semibold text-slate-800 hover:text-blue-700">{{ $p->nama }}</a>
                                <p class="mt-0.5 text-xs text-slate-400">{{ $p->nip }}</p>
                            </td>
                            <td class="px-5 py-4 text-slate-600">{{ $p->pangkat_golongan ?? '-' }}</td>
                            <td class="px-5 py-4 text-slate-600">{{ $p->jabatan ?? '-' }}</td>
                            <td class="px-5 py-4">
                                @if ($next)
                                    <span class="text-slate-600">{{ $next->translatedFormat('d M Y') }}</span>
                                    @if ($late)
                                        <span
                                            class="ml-1 rounded-full bg-red-50 px-2 py-0.5 text-[10px] font-semibold text-red-600">Terlewat</span>
                                    @elseif ($soon)
                                        <span
                                            class="ml-1 rounded-full bg-amber-50 px-2 py-0.5 text-[10px] font-semibold text-amber-600">Segera</span>
                                    @endif
                                @else
                                    <span class="text-slate-300">Belum ada</span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <span @class([
                                    'rounded-full px-2.5 py-1 text-xs font-medium capitalize',
                                    'bg-emerald-50 text-emerald-600' => $p->status === 'aktif',
                                    'bg-slate-100 text-slate-500' => $p->status !== 'aktif',
                                ])>{{ $p->status }}</span>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex justify-end gap-1">
                                    <a href="{{ route('pegawai.show', $p) }}"
                                        class="rounded-lg px-3 py-1.5 text-xs text-blue-600 hover:bg-blue-50">Detail</a>
                                    <a href="{{ route('pegawai.edit', $p) }}"
                                        class="rounded-lg px-3 py-1.5 text-xs text-slate-600 hover:bg-slate-100">Ubah</a>
                                    <form method="POST" action="{{ route('pegawai.destroy', $p) }}"
                                        data-confirm="Data {{ $p->nama }} akan dihapus.">
                                        @csrf @method('DELETE')
                                        <button
                                            class="rounded-lg px-3 py-1.5 text-xs text-red-500 hover:bg-red-50">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center text-sm text-slate-400">Belum ada data pegawai.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($pegawais->hasPages())
            <div class="border-t border-slate-100 p-5">{{ $pegawais->links() }}</div>
        @endif
    </div>
@endsection
