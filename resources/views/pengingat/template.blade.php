@extends('layouts.app')

@section('title', 'Template Pesan')
@section('page-title', 'Template Pesan')

@php
    $input =
        'mt-1.5 w-full rounded-xl border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 focus:border-blue-500 focus:ring-blue-500';
    $label = 'block text-sm font-medium text-slate-600';
    $variabel = \App\Services\PesanBuilder::variabel();
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

    <datalist id="daftar-variabel">
        @foreach ($variabel as $kode => $arti)
            <option value="{{ $kode }}">{{ $arti }}</option>
        @endforeach
    </datalist>

    <div x-data="{
        modal: false,
        isEdit: false,
        action: '',
        form: { params: [] },
        openCreate() {
            this.isEdit = false;
            this.action = '{{ route('pengingat.template.store') }}';
            this.form = { nama: '', subjek: 'Pengingat Kenaikan Gaji Berkala (KGB)', isi: '', wa_template: '', wa_bahasa: 'id', params: [] };
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
                <h1 class="text-2xl font-bold text-slate-800">Template Pesan</h1>
                <p class="mt-1 text-sm text-slate-400">Isi pesan dipakai untuk Fonnte dan email. Template WhatsApp hanya
                    diperlukan jika memakai KirimDev.</p>
            </div>
            <button @click="openCreate()"
                class="rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">+ Tambah
                Template</button>
        </div>

        <div class="grid gap-5 lg:grid-cols-2">
            @forelse ($templates as $t)
                <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h3 class="font-semibold text-slate-800">{{ $t->nama }}</h3>
                            <p class="mt-0.5 text-xs text-slate-400">Subjek email: {{ $t->subjek }} · dipakai
                                {{ $t->aturan_count }} aturan</p>
                        </div>
                        <div class="flex gap-1">
                            <button type="button"
                                @click="openEdit('{{ route('pengingat.template.update', $t) }}', @js([
    'nama' => $t->nama,
    'subjek' => $t->subjek,
    'isi' => $t->isi,
    'wa_template' => $t->wa_template ?? '',
    'wa_bahasa' => $t->wa_bahasa ?: 'id',
    'params' => collect($t->wa_params ?? [])
        ->map(fn($p) => ['nama' => $p['nama'] ?? '', 'nilai' => $p['nilai'] ?? ''])
        ->values()
        ->all(),
]))"
                                class="rounded-lg px-3 py-1.5 text-xs text-slate-600 hover:bg-slate-100">Ubah</button>
                            <form method="POST" action="{{ route('pengingat.template.destroy', $t) }}"
                                data-confirm="Template {{ $t->nama }} akan dihapus.">
                                @csrf @method('DELETE')
                                <button class="rounded-lg px-3 py-1.5 text-xs text-red-500 hover:bg-red-50">Hapus</button>
                            </form>
                        </div>
                    </div>

                    <div class="mt-4 rounded-xl bg-emerald-50 p-3 text-xs text-emerald-700">
                        @if ($t->wa_template)
                            <b>WhatsApp:</b> template <code>{{ $t->wa_template }}</code> ({{ $t->wa_bahasa }}) ·
                            {{ count($t->wa_params ?? []) }} parameter
                            @foreach ($t->wa_params ?? [] as $i => $p)
                                <span
                                    class="ml-1 inline-block rounded bg-white px-1.5 py-0.5">{{ $p['nama'] ?? null ?: 'param ' . ($i + 1) }}
                                    = {{ $p['nilai'] }}</span>
                            @endforeach
                        @else
                            @if (\App\Models\Setting::waProvider() === 'kirimdev')
                                <span class="text-amber-700">Belum ada template WhatsApp, pengingat hanya bisa lewat
                                    email.</span>
                            @else
                                <span class="text-slate-500">Tanpa template WhatsApp KirimDev (tidak diperlukan untuk Fonnte
                                    atau email).</span>
                            @endif
                        @endif
                    </div>

                    <p class="mt-3 whitespace-pre-line rounded-xl bg-slate-50 p-4 text-sm text-slate-600">
                        {{ $t->isi }}</p>
                </div>
            @empty
                <div
                    class="rounded-2xl border border-slate-100 bg-white p-12 text-center text-sm text-slate-400 shadow-sm lg:col-span-2">
                    Belum ada template pesan.
                </div>
            @endforelse
        </div>

        {{-- MODAL --}}
        <div x-show="modal" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/40" @click="modal = false"></div>

            <form method="POST" :action="action"
                class="relative max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-2xl bg-white p-6 shadow-xl">
                @csrf
                <template x-if="isEdit"><input type="hidden" name="_method" value="PUT"></template>

                <h3 class="text-lg font-semibold text-slate-800" x-text="isEdit ? 'Ubah Template' : 'Tambah Template'"></h3>

                <div class="mt-5 space-y-4">
                    <div>
                        <label class="{{ $label }}">Nama template *</label>
                        <input name="nama" x-model="form.nama" class="{{ $input }}" required>
                    </div>

                    <div class="rounded-xl border border-slate-100 p-4">
                        <p class="text-sm font-semibold text-slate-700">Pesan email (cadangan)</p>
                        <div class="mt-3 space-y-4">
                            <div>
                                <label class="{{ $label }}">Subjek email *</label>
                                <input name="subjek" x-model="form.subjek" class="{{ $input }}" required>
                            </div>
                            <div>
                                <label class="{{ $label }}">Isi pesan *</label>
                                <textarea name="isi" x-model="form.isi" rows="6" class="{{ $input }}" required></textarea>
                                <p class="mt-2 text-xs text-slate-400">Klik variabel untuk menambahkannya ke isi pesan:</p>
                                <div class="mt-2 flex flex-wrap gap-1.5">
                                    @foreach ($variabel as $kode => $arti)
                                        <button type="button" title="{{ $arti }}"
                                            @click="form.isi = (form.isi || '') + @js($kode)"
                                            class="rounded-lg bg-blue-50 px-2 py-1 text-xs text-blue-600 hover:bg-blue-100">{{ $kode }}</button>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-xl border border-emerald-100 bg-emerald-50/40 p-4">
                        <p class="text-sm font-semibold text-slate-700">Template WhatsApp (KirimDev)</p>
                        <p class="mt-1 text-xs text-slate-500">Isi sesuai template yang sudah <b>disetujui</b> di dashboard
                            KirimDev. Kosongkan jika memakai Fonnte.</p>

                        <div class="mt-3 grid gap-4 sm:grid-cols-3">
                            <div class="sm:col-span-2">
                                <label class="{{ $label }}">Nama template</label>
                                <input name="wa_template" x-model="form.wa_template" placeholder="pengingat_kgb"
                                    class="{{ $input }}">
                            </div>
                            <div>
                                <label class="{{ $label }}">Bahasa</label>
                                <input name="wa_bahasa" x-model="form.wa_bahasa" placeholder="id"
                                    class="{{ $input }}">
                            </div>
                        </div>

                        <p class="mb-2 mt-4 text-sm font-medium text-slate-600">Parameter (urut sesuai
                            @{{ 1 }}, @{{ 2 }}, dst. di template)</p>

                        <div class="space-y-2">
                            <template x-for="(p, i) in form.params" :key="i">
                                <div class="flex gap-2">
                                    <input :name="'params[' + i + '][nama]'" x-model="p.nama"
                                        placeholder="nama parameter (kosongkan jika berdasarkan urutan)"
                                        class="w-2/5 rounded-xl border-slate-200 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
                                    <input :name="'params[' + i + '][nilai]'" x-model="p.nilai" list="daftar-variabel"
                                        placeholder="nilai, mis. {nama}"
                                        class="w-3/5 rounded-xl border-slate-200 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500"
                                        required>
                                    <button type="button" @click="form.params.splice(i, 1)"
                                        class="rounded-lg px-2 text-red-500 hover:bg-red-50">&times;</button>
                                </div>
                            </template>
                        </div>

                        <button type="button" @click="form.params.push({ nama: '', nilai: '' })"
                            class="mt-3 rounded-lg bg-white px-3 py-1.5 text-xs font-medium text-blue-600 shadow-sm hover:bg-blue-50">+
                            Tambah parameter</button>
                        <p class="mt-2 text-xs text-slate-400">Isi kolom nama hanya jika template memakai parameter bernama
                            (mis. <code>customer_name</code>). Semua baris harus berpola sama.</p>
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
