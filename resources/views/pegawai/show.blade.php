@extends('layouts.app')

@section('title', $pegawai->nama)
@section('page-title', 'Detail Pegawai')

@php
    $input =
        'mt-1.5 w-full rounded-xl border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 focus:border-blue-500 focus:ring-blue-500';
    $label = 'block text-sm font-medium text-slate-600';
    $kgbTerakhir = $pegawai->riwayatKgb->first();
    $blank = [
        'no_sk' => '',
        'tanggal_sk' => '',
        'pejabat_penandatangan' => '',
        'tmt_kgb' => '',
        'golongan_ruang' => $pegawai->pangkat_golongan,
        'masa_kerja_tahun' => 0,
        'masa_kerja_bulan' => 0,
        'gaji_pokok_lama' => $kgbTerakhir?->gaji_pokok_baru ?? 0,
        'gaji_pokok_baru' => '',
        'keterangan' => '',
    ];
    $hasKgbErrors = $errors->hasAny([
        'no_sk',
        'tanggal_sk',
        'tmt_kgb',
        'gaji_pokok_baru',
        'gaji_pokok_lama',
        'masa_kerja_tahun',
        'masa_kerja_bulan',
        'file_sk',
    ]);
@endphp

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">{{ $pegawai->nama }}</h1>
            <p class="mt-1 text-sm text-slate-400">NIP {{ $pegawai->nip }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('pegawai.index') }}" class="rounded-xl px-4 py-2.5 text-sm text-slate-500 hover:bg-white">&larr;
                Kembali</a>
            <a href="{{ route('pegawai.edit', $pegawai) }}"
                class="rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">Ubah Data</a>
        </div>
    </div>

    <div x-data="{
        tab: '{{ $hasKgbErrors ? 'kgb' : 'pribadi' }}',
        modal: {{ $hasKgbErrors ? 'true' : 'false' }},
        action: '{{ route('kgb.store', $pegawai) }}',
        isEdit: false,
        form: @js($hasKgbErrors ? old() : $blank),
        openCreate() {
            this.isEdit = false;
            this.action = '{{ route('kgb.store', $pegawai) }}';
            this.form = @js($blank);
            this.modal = true;
        },
        openEdit(kgb) {
            this.isEdit = true;
            this.action = kgb.url;
            this.form = kgb.data;
            this.modal = true;
        }
    }">

        <div class="mb-5 flex gap-1 rounded-xl bg-white p-1 shadow-sm sm:w-fit">
            <button @click="tab = 'pribadi'"
                :class="tab === 'pribadi' ? 'bg-blue-50 font-semibold text-blue-700' : 'text-slate-500'"
                class="rounded-lg px-5 py-2 text-sm">Data Pribadi</button>
            <button @click="tab = 'kgb'"
                :class="tab === 'kgb' ? 'bg-blue-50 font-semibold text-blue-700' : 'text-slate-500'"
                class="rounded-lg px-5 py-2 text-sm">
                Riwayat KGB
                <span
                    class="ml-1 rounded-full bg-slate-100 px-2 py-0.5 text-[10px] text-slate-500">{{ $pegawai->riwayatKgb->count() }}</span>
            </button>
        </div>

        {{-- TAB: DATA PRIBADI --}}
        <div x-show="tab === 'pribadi'" class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
            <dl class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ([
            'NIP' => $pegawai->nip,
            'Nama' => $pegawai->nama,
            'Tempat, Tanggal Lahir' => trim(($pegawai->tempat_lahir ? $pegawai->tempat_lahir . ', ' : '') . $pegawai->tanggal_lahir->translatedFormat('d F Y')),
            'Jenis Kelamin' => $pegawai->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan',
            'Pangkat / Golongan' => $pegawai->pangkat_golongan,
            'Jabatan' => $pegawai->jabatan,
            'Unit Kerja' => $pegawai->unit_kerja,
            'TMT CPNS' => $pegawai->tmt_cpns?->translatedFormat('d F Y'),
            'Status' => ucfirst($pegawai->status),
            'No. HP' => $pegawai->no_hp,
            'Email' => $pegawai->email,
        ] as $k => $v)
                    <div>
                        <dt class="text-xs uppercase tracking-wider text-slate-400">{{ $k }}</dt>
                        <dd class="mt-1 text-sm font-medium text-slate-700">{{ $v ?: '-' }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>

        {{-- TAB: RIWAYAT KGB --}}
        <div x-show="tab === 'kgb'" x-cloak class="rounded-2xl border border-slate-100 bg-white shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 p-5">
                <div>
                    <h3 class="font-semibold text-slate-800">Riwayat Kenaikan Gaji Berkala</h3>
                    @if ($kgbTerakhir)
                        <p class="mt-1 text-xs text-slate-400">KGB berikutnya:
                            {{ $kgbTerakhir->tmt_kgb_berikutnya->translatedFormat('d F Y') }}</p>
                    @endif
                </div>
                <button @click="openCreate()"
                    class="rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">+ Tambah
                    KGB</button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-400">
                        <tr>
                            <th class="px-5 py-3 font-semibold">TMT KGB</th>
                            <th class="px-5 py-3 font-semibold">No. SK</th>
                            <th class="px-5 py-3 font-semibold">Gol.</th>
                            <th class="px-5 py-3 font-semibold">Masa Kerja</th>
                            <th class="px-5 py-3 font-semibold">Gaji Lama</th>
                            <th class="px-5 py-3 font-semibold">Gaji Baru</th>
                            <th class="px-5 py-3 font-semibold">KGB Berikutnya</th>
                            <th class="px-5 py-3 text-right font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse ($pegawai->riwayatKgb as $kgb)
                            <tr class="hover:bg-slate-50/60">
                                <td class="px-5 py-4 font-medium text-slate-700">
                                    {{ $kgb->tmt_kgb->translatedFormat('d M Y') }}</td>
                                <td class="px-5 py-4 text-slate-600">
                                    {{ $kgb->no_sk }}
                                    <p class="text-xs text-slate-400">{{ $kgb->tanggal_sk->translatedFormat('d M Y') }}</p>
                                </td>
                                <td class="px-5 py-4 text-slate-600">{{ $kgb->golongan_ruang ?? '-' }}</td>
                                <td class="px-5 py-4 text-slate-600">{{ $kgb->masa_kerja_tahun }} th
                                    {{ $kgb->masa_kerja_bulan }} bln</td>
                                <td class="px-5 py-4 text-slate-600">Rp
                                    {{ number_format($kgb->gaji_pokok_lama, 0, ',', '.') }}</td>
                                <td class="px-5 py-4 font-semibold text-slate-800">Rp
                                    {{ number_format($kgb->gaji_pokok_baru, 0, ',', '.') }}</td>
                                <td class="px-5 py-4 text-slate-600">
                                    {{ $kgb->tmt_kgb_berikutnya->translatedFormat('d M Y') }}</td>
                                <td class="px-5 py-4">
                                    <div class="flex justify-end gap-1">
                                        @if ($kgb->file_sk)
                                            <a href="{{ asset('storage/' . $kgb->file_sk) }}" target="_blank"
                                                class="rounded-lg px-3 py-1.5 text-xs text-blue-600 hover:bg-blue-50">SK</a>
                                        @endif
                                        <button type="button"
                                            @click="openEdit({
                                                url: '{{ route('kgb.update', [$pegawai, $kgb]) }}',
                                                data: @js([
    'no_sk' => $kgb->no_sk,
    'tanggal_sk' => $kgb->tanggal_sk->format('Y-m-d'),
    'pejabat_penandatangan' => $kgb->pejabat_penandatangan,
    'tmt_kgb' => $kgb->tmt_kgb->format('Y-m-d'),
    'golongan_ruang' => $kgb->golongan_ruang,
    'masa_kerja_tahun' => $kgb->masa_kerja_tahun,
    'masa_kerja_bulan' => $kgb->masa_kerja_bulan,
    'gaji_pokok_lama' => $kgb->gaji_pokok_lama,
    'gaji_pokok_baru' => $kgb->gaji_pokok_baru,
    'keterangan' => $kgb->keterangan,
])
                                            })"
                                            class="rounded-lg px-3 py-1.5 text-xs text-slate-600 hover:bg-slate-100">Ubah</button>
                                        <form method="POST" action="{{ route('kgb.destroy', [$pegawai, $kgb]) }}"
                                            data-confirm="Riwayat KGB TMT {{ $kgb->tmt_kgb->translatedFormat('d M Y') }} akan dihapus.">
                                            @csrf @method('DELETE')
                                            <button
                                                class="rounded-lg px-3 py-1.5 text-xs text-red-500 hover:bg-red-50">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-5 py-12 text-center text-sm text-slate-400">Belum ada riwayat
                                    KGB.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- MODAL FORM KGB --}}
        <div x-show="modal" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/40" @click="modal = false"></div>

            <form method="POST" :action="action" enctype="multipart/form-data"
                class="relative max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-2xl bg-white p-6 shadow-xl">
                @csrf
                <template x-if="isEdit"><input type="hidden" name="_method" value="PUT"></template>

                <h3 class="text-lg font-semibold text-slate-800"
                    x-text="isEdit ? 'Ubah Riwayat KGB' : 'Tambah Riwayat KGB'"></h3>

                @if ($errors->any() && $hasKgbErrors)
                    <div class="mt-4 rounded-xl bg-red-50 p-3 text-sm text-red-600">
                        <ul class="list-inside list-disc">
                            @foreach ($errors->all() as $e)
                                <li>{{ $e }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="{{ $label }}">No. SK *</label>
                        <input name="no_sk" x-model="form.no_sk" class="{{ $input }}" required>
                    </div>
                    <div>
                        <label class="{{ $label }}">Tanggal SK *</label>
                        <input type="date" name="tanggal_sk" x-model="form.tanggal_sk" class="{{ $input }}"
                            required>
                    </div>
                    <div>
                        <label class="{{ $label }}">Pejabat Penandatangan</label>
                        <input name="pejabat_penandatangan" x-model="form.pejabat_penandatangan"
                            class="{{ $input }}">
                    </div>
                    <div>
                        <label class="{{ $label }}">TMT KGB *</label>
                        <input type="date" name="tmt_kgb" x-model="form.tmt_kgb" class="{{ $input }}" required>
                        <p class="mt-1 text-xs text-slate-400">KGB berikutnya otomatis +2 tahun.</p>
                    </div>
                    <div>
                        <label class="{{ $label }}">Golongan / Ruang</label>
                        <input name="golongan_ruang" x-model="form.golongan_ruang" class="{{ $input }}">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="{{ $label }}">Masa Kerja (th) *</label>
                            <input type="number" min="0" max="50" name="masa_kerja_tahun"
                                x-model="form.masa_kerja_tahun" class="{{ $input }}" required>
                        </div>
                        <div>
                            <label class="{{ $label }}">(bln) *</label>
                            <input type="number" min="0" max="11" name="masa_kerja_bulan"
                                x-model="form.masa_kerja_bulan" class="{{ $input }}" required>
                        </div>
                    </div>
                    <div>
                        <label class="{{ $label }}">Gaji Pokok Lama (Rp) *</label>
                        <input type="number" min="0" name="gaji_pokok_lama" x-model="form.gaji_pokok_lama"
                            class="{{ $input }}" required>
                    </div>
                    <div>
                        <label class="{{ $label }}">Gaji Pokok Baru (Rp) *</label>
                        <input type="number" min="0" name="gaji_pokok_baru" x-model="form.gaji_pokok_baru"
                            class="{{ $input }}" required>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="{{ $label }}">File SK (PDF/JPG/PNG, maks 2 MB)</label>
                        <input type="file" name="file_sk" accept=".pdf,.jpg,.jpeg,.png"
                            class="mt-1.5 w-full rounded-xl border border-slate-200 text-sm file:mr-3 file:rounded-l-xl file:border-0 file:bg-blue-50 file:px-4 file:py-2.5 file:text-blue-700">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="{{ $label }}">Keterangan</label>
                        <textarea name="keterangan" rows="2" x-model="form.keterangan" class="{{ $input }}"></textarea>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" @click="modal = false"
                        class="rounded-xl px-5 py-2.5 text-sm text-slate-500 hover:bg-slate-50">Batal</button>
                    <button type="submit"
                        class="rounded-xl bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>
@endsection
