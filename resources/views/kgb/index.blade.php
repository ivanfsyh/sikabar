@extends('layouts.app')

@section('title', 'Riwayat KGB')
@section('page-title', 'Riwayat KGB')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-800">Riwayat KGB</h1>
        <p class="mt-1 text-sm text-slate-400">Seluruh riwayat kenaikan gaji berkala pegawai. Untuk menambah data, buka
            detail pegawai.</p>
    </div>

    <div class="rounded-2xl border border-slate-100 bg-white shadow-sm">
        <form method="GET" class="flex gap-3 border-b border-slate-100 p-5">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari NIP atau nama pegawai..."
                class="w-full rounded-xl border-slate-200 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-blue-500 sm:w-80">
            <button
                class="rounded-xl bg-slate-800 px-5 py-2.5 text-sm font-medium text-white hover:bg-slate-700">Cari</button>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-400">
                    <tr>
                        <th class="px-5 py-3 font-semibold">Pegawai</th>
                        <th class="px-5 py-3 font-semibold">TMT KGB</th>
                        <th class="px-5 py-3 font-semibold">No. SK</th>
                        <th class="px-5 py-3 font-semibold">Gaji Baru</th>
                        <th class="px-5 py-3 font-semibold">KGB Berikutnya</th>
                        <th class="px-5 py-3 text-right font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse ($riwayat as $r)
                        <tr class="hover:bg-slate-50/60">
                            <td class="px-5 py-4">
                                <p class="font-semibold text-slate-800">{{ $r->pegawai->nama }}</p>
                                <p class="text-xs text-slate-400">{{ $r->pegawai->nip }}</p>
                            </td>
                            <td class="px-5 py-4 text-slate-600">{{ $r->tmt_kgb->translatedFormat('d M Y') }}</td>
                            <td class="px-5 py-4 text-slate-600">{{ $r->no_sk }}</td>
                            <td class="px-5 py-4 font-semibold text-slate-800">Rp
                                {{ number_format($r->gaji_pokok_baru, 0, ',', '.') }}</td>
                            <td class="px-5 py-4 text-slate-600">{{ $r->tmt_kgb_berikutnya->translatedFormat('d M Y') }}
                            </td>
                            <td class="px-5 py-4 text-right">
                                <a href="{{ route('pegawai.show', $r->pegawai) }}"
                                    class="rounded-lg px-3 py-1.5 text-xs text-blue-600 hover:bg-blue-50">Lihat Pegawai</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center text-sm text-slate-400">Belum ada riwayat KGB.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($riwayat->hasPages())
            <div class="border-t border-slate-100 p-5">{{ $riwayat->links() }}</div>
        @endif
    </div>
@endsection
