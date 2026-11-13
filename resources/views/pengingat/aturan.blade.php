@extends('layouts.app')

@section('title', 'Aturan Pengingat')
@section('page-title', 'Aturan Pengingat')

@php
    $input =
        'mt-1.5 w-full rounded-xl border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 focus:border-blue-500 focus:ring-blue-500';
    $label = 'block text-sm font-medium text-slate-600';
@endphp

@section('content')
    @if ($errors->any())
        <div class="mb-5 rounded-xl bg-red-50 p-4 text-sm text-red-600">
            <ul class="list-inside list-disc">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div x-data="{
        modal: false,
        isEdit: false,
        action: '',
        form: {},
        openCreate() {
            this.isEdit = false;
            this.action = '{{ route('pengingat.aturan.store') }}';
            this.form = { nama: '', hari_sebelum: 30, template_pesan_id: '{{ $templates->first()?->id }}', aktif: true };
            this.modal = true;
        },
        openEdit(url, data) {
            this.isEdit = true;
            this.action = url;
            this.form = data;
            this.modal = true;
        }
    }">

        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Aturan Pengingat</h1>
                <p class="mt-1 text-sm text-slate-400">Atur berapa kali pengingat dikirim dan kapan waktunya relatif terhadap
                    tanggal KGB.</p>
            </div>
            @if ($templates->isEmpty())
                <a href="{{ route('pengingat.template.index') }}"
                    class="rounded-xl bg-amber-500 px-4 py-2.5 text-sm font-semibold text-white">Buat template dulu</a>
            @else
                <button @click="openCreate()"
                    class="rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">+ Tambah
                    Aturan</button>
            @endif
        </div>

        <div class="rounded-2xl border border-slate-100 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-400">
                        <tr>
                            <th class="px-5 py-3 font-semibold">Aturan</th>
                            <th class="px-5 py-3 font-semibold">Waktu Kirim</th>
                            <th class="px-5 py-3 font-semibold">Template</th>
                            <th class="px-5 py-3 font-semibold">Status</th>
                            <th class="px-5 py-3 text-right font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse ($aturan as $a)
                            <tr class="hover:bg-slate-50/60">
                                <td class="px-5 py-4 font-semibold text-slate-800">{{ $a->nama }}</td>
                                <td class="px-5 py-4">
                                    <span
                                        class="rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-600">{{ $a->label }}</span>
                                    <span class="ml-2 text-xs text-slate-400">
                                        @if ($a->hari_sebelum > 0)
                                            {{ $a->hari_sebelum }} hari sebelum KGB
                                        @elseif ($a->hari_sebelum === 0)
                                            pada hari KGB
                                        @else
                                            {{ abs($a->hari_sebelum) }} hari setelah KGB
                                        @endif
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-slate-600">{{ $a->template?->nama ?? '-' }}</td>
                                <td class="px-5 py-4">
                                    <form method="POST" action="{{ route('pengingat.aturan.toggle', $a) }}">
                                        @csrf @method('PATCH')
                                        <button
                                            @class([
                                                'rounded-full px-2.5 py-1 text-xs font-medium',
                                                'bg-emerald-50 text-emerald-600' => $a->aktif,
                                                'bg-slate-100 text-slate-500' => !$a->aktif,
                                            ])>{{ $a->aktif ? 'Aktif' : 'Nonaktif' }}</button>
                                    </form>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex justify-end gap-1">
                                        <button type="button"
                                            @click="openEdit('{{ route('pengingat.aturan.update', $a) }}', @js([
    'nama' => $a->nama,
    'hari_sebelum' => $a->hari_sebelum,
    'template_pesan_id' => (string) $a->template_pesan_id,
    'aktif' => $a->aktif,
]))"
                                            class="rounded-lg px-3 py-1.5 text-xs text-slate-600 hover:bg-slate-100">Ubah</button>
                                        <form method="POST" action="{{ route('pengingat.aturan.destroy', $a) }}"
                                            data-confirm="Aturan {{ $a->nama }} akan dihapus. Riwayat pengiriman tetap tersimpan.">
                                            @csrf @method('DELETE')
                                            <button
                                                class="rounded-lg px-3 py-1.5 text-xs text-red-500 hover:bg-red-50">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-12 text-center text-sm text-slate-400">Belum ada aturan
                                    pengingat.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <p class="mt-4 text-xs text-slate-400">
            Contoh: aturan 30, 14, 7, dan 0 hari berarti pegawai menerima 4 pengingat. Isi angka negatif (mis. -3) untuk
            pengingat susulan setelah tanggal KGB terlewat.
        </p>

        {{-- MODAL --}}
        <div x-show="modal" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/40" @click="modal = false"></div>

            <form method="POST" :action="action" class="relative w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl">
                @csrf
                <template x-if="isEdit"><input type="hidden" name="_method" value="PUT"></template>

                <h3 class="text-lg font-semibold text-slate-800" x-text="isEdit ? 'Ubah Aturan' : 'Tambah Aturan'"></h3>

                <div class="mt-5 space-y-4">
                    <div>
                        <label class="{{ $label }}">Nama aturan *</label>
                        <input name="nama" x-model="form.nama" class="{{ $input }}"
                            placeholder="contoh: Pengingat 30 hari" required>
                    </div>
                    <div>
                        <label class="{{ $label }}">Kirim berapa hari sebelum tanggal KGB? *</label>
                        <input type="number" name="hari_sebelum" x-model="form.hari_sebelum" min="-365" max="365"
                            class="{{ $input }}" required>
                        <p class="mt-1 text-xs text-slate-400">30 = H-30 · 0 = pada hari KGB · -3 = 3 hari setelah KGB.</p>
                    </div>
                    <div>
                        <label class="{{ $label }}">Template pesan *</label>
                        <select name="template_pesan_id" x-model="form.template_pesan_id" class="{{ $input }}"
                            required>
                            @foreach ($templates as $t)
                                <option value="{{ $t->id }}">{{ $t->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <label class="flex items-center gap-2 text-sm text-slate-600">
                        <input type="hidden" name="aktif" value="0">
                        <input type="checkbox" name="aktif" value="1" :checked="form.aktif"
                            class="rounded border-slate-300 text-blue-600">
                        Aktifkan aturan ini
                    </label>
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
