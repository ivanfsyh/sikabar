<div class="mt-8 rounded-2xl border border-slate-100 bg-white shadow-sm">
    <div class="flex items-center justify-between border-b border-slate-100 p-5">
        <div>
            <h3 class="font-semibold text-slate-800">Jadwal KGB Terdekat</h3>
            <p class="mt-1 text-xs text-slate-400">Pegawai aktif yang KGB-nya terlewat, bulan ini, atau 3 bulan ke depan.
            </p>
        </div>
        @if ($detail ?? false)
            <a href="{{ route('kgb.index') }}" class="text-sm text-blue-600 hover:text-blue-700">Lihat semua &rarr;</a>
        @endif
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-400">
                <tr>
                    <th class="px-5 py-3 font-semibold">Pegawai</th>
                    <th class="px-5 py-3 font-semibold">Golongan</th>
                    <th class="px-5 py-3 font-semibold">Jabatan</th>
                    <th class="px-5 py-3 font-semibold">KGB Berikutnya</th>
                    <th class="px-5 py-3 font-semibold">Keterangan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse ($daftarJadwal as $j)
                    @php
                        $p = $j['pegawai'];
                        $tgl = $j['tanggal'];
                        $terlewat = $tgl->lt($awalBulan);
                        $bulanIni = $tgl->isSameMonth(now());
                    @endphp
                    <tr class="hover:bg-slate-50/60">
                        <td class="px-5 py-4">
                            @if ($detail ?? false)
                                <a href="{{ route('pegawai.show', $p) }}"
                                    class="font-semibold text-slate-800 hover:text-blue-700">{{ $p->nama }}</a>
                            @else
                                <span class="font-semibold text-slate-800">{{ $p->nama }}</span>
                            @endif
                            <p class="mt-0.5 text-xs text-slate-400">{{ $p->nip }}</p>
                        </td>
                        <td class="px-5 py-4 text-slate-600">{{ $p->pangkat_golongan ?? '-' }}</td>
                        <td class="px-5 py-4 text-slate-600">{{ $p->jabatan ?? '-' }}</td>
                        <td class="px-5 py-4 text-slate-600">{{ $tgl->translatedFormat('d F Y') }}</td>
                        <td class="px-5 py-4">
                            @if ($terlewat)
                                <span
                                    class="rounded-full bg-red-50 px-2.5 py-1 text-xs font-medium text-red-600">Terlewat</span>
                            @elseif ($bulanIni)
                                <span
                                    class="rounded-full bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-600">Bulan
                                    ini</span>
                            @else
                                <span class="rounded-full bg-amber-50 px-2.5 py-1 text-xs font-medium text-amber-600">
                                    {{ $tgl->diffForHumans(now(), ['parts' => 1]) }}
                                </span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-12 text-center text-sm text-slate-400">
                            Tidak ada jadwal KGB dalam 3 bulan ke depan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
