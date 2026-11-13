@extends('layouts.app')

@section('title', 'Riwayat Pengiriman')
@section('page-title', 'Riwayat Pengiriman')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-800">Riwayat Pengiriman</h1>
        <p class="mt-1 text-sm text-slate-400">Catatan seluruh pengingat yang dijadwalkan dan dikirim sistem.</p>
    </div>

    <div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ([['Terkirim', 'terkirim', 'text-emerald-600'], ['Menunggu / Diproses', null, 'text-amber-600'], ['Gagal', 'gagal', 'text-red-500'], ['Dilewati', 'dilewati', 'text-slate-500']] as [$judul, $kunci, $warna])
            <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
                <p class="text-sm text-slate-400">{{ $judul }}</p>
                <h3 class="mt-1 text-2xl font-bold {{ $warna }}">
                    {{ $kunci ? $ringkasan[$kunci] ?? 0 : ($ringkasan['menunggu'] ?? 0) + ($ringkasan['diproses'] ?? 0) }}
                </h3>
            </div>
        @endforeach
    </div>

    <div class="rounded-2xl border border-slate-100 bg-white shadow-sm">
        <form method="GET" class="flex flex-wrap gap-3 border-b border-slate-100 p-5">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari NIP atau nama..."
                class="w-full rounded-xl border-slate-200 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-blue-500 sm:w-72">
            <select name="status"
                class="rounded-xl border-slate-200 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="">Semua Status</option>
                @foreach (['terkirim', 'menunggu', 'diproses', 'gagal', 'dilewati'] as $s)
                    <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
            <button
                class="rounded-xl bg-slate-800 px-5 py-2.5 text-sm font-medium text-white hover:bg-slate-700">Cari</button>
            @if (request()->hasAny(['q', 'status']))
                <a href="{{ route('pengingat.riwayat.index') }}"
                    class="px-3 py-2.5 text-sm text-slate-400 hover:text-slate-600">Reset</a>
            @endif
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-400">
                    <tr>
                        <th class="px-5 py-3 font-semibold">Waktu</th>
                        <th class="px-5 py-3 font-semibold">Pegawai</th>
                        <th class="px-5 py-3 font-semibold">Aturan</th>
                        <th class="px-5 py-3 font-semibold">KGB</th>
                        <th class="px-5 py-3 font-semibold">Kanal</th>
                        <th class="px-5 py-3 font-semibold">Status</th>
                        <th class="px-5 py-3 text-right font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse ($riwayat as $r)
                        <tr class="align-top hover:bg-slate-50/60">
                            <td class="px-5 py-4 text-slate-600">
                                {{ ($r->terkirim_at ?? $r->created_at)->translatedFormat('d M Y') }}
                                <p class="text-xs text-slate-400">{{ ($r->terkirim_at ?? $r->created_at)->format('H:i') }}
                                </p>
                            </td>
                            <td class="px-5 py-4">
                                <p class="font-semibold text-slate-800">{{ $r->pegawai?->nama ?? '-' }}</p>
                                <p class="text-xs text-slate-400">{{ $r->pegawai?->nip }}</p>
                            </td>
                            <td class="px-5 py-4 text-slate-600">{{ $r->nama_aturan ?? '-' }}</td>
                            <td class="px-5 py-4 text-slate-600">{{ $r->tmt_jatuh_tempo->translatedFormat('d M Y') }}</td>
                            <td class="px-5 py-4 text-slate-600">
                                @if ($r->kanal === 'wa')
                                    WhatsApp
                                @elseif ($r->kanal === 'email')
                                    Email (cadangan)
                                @else
                                    -
                                @endif
                                @if ($r->tujuan)
                                    <p class="text-xs text-slate-400">{{ $r->tujuan }}</p>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <span @class([
                                    'rounded-full px-2.5 py-1 text-xs font-medium capitalize',
                                    'bg-emerald-50 text-emerald-600' => $r->status === 'terkirim',
                                    'bg-red-50 text-red-600' => $r->status === 'gagal',
                                    'bg-amber-50 text-amber-600' => in_array($r->status, [
                                        'menunggu',
                                        'diproses',
                                    ]),
                                    'bg-slate-100 text-slate-500' => $r->status === 'dilewati',
                                ])>{{ $r->status }}</span>
                                @if ($r->error)
                                    <p class="mt-1 max-w-xs text-xs text-red-500">{{ $r->error }}</p>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right">
                                @if ($r->status === 'gagal')
                                    <form method="POST" action="{{ route('pengingat.riwayat.kirim-ulang', $r) }}">
                                        @csrf
                                        <button class="rounded-lg px-3 py-1.5 text-xs text-blue-600 hover:bg-blue-50">Kirim
                                            ulang</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center text-sm text-slate-400">Belum ada riwayat
                                pengiriman.</td>
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
