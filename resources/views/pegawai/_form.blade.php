@php
    $input =
        'mt-1.5 w-full rounded-xl border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 placeholder-slate-300 focus:border-blue-500 focus:ring-blue-500';
    $label = 'block text-sm font-medium text-slate-600';
    $field = fn($name, $default = null) => old($name, $pegawai->{$name} ?? $default);
@endphp

<div class="grid gap-5 md:grid-cols-2">

    <div class="md:col-span-2">
        <label for="nip" class="{{ $label }}">NIP <span class="text-red-500">*</span></label>
        <input id="nip" name="nip" type="text" inputmode="numeric" maxlength="18" value="{{ $field('nip') }}"
            placeholder="18 digit angka" class="{{ $input }}" required>
        <p class="mt-1 text-xs text-slate-400">Format: tanggal lahir (8 digit) + TMT CPNS (6 digit) + jenis kelamin (1
            digit) + nomor urut (3 digit).</p>
        @error('nip')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>

    <div class="md:col-span-2">
        <label for="nama" class="{{ $label }}">Nama Lengkap <span class="text-red-500">*</span></label>
        <input id="nama" name="nama" type="text" value="{{ $field('nama') }}" class="{{ $input }}"
            required>
        @error('nama')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="tempat_lahir" class="{{ $label }}">Tempat Lahir</label>
        <input id="tempat_lahir" name="tempat_lahir" type="text" value="{{ $field('tempat_lahir') }}"
            class="{{ $input }}">
        @error('tempat_lahir')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="tanggal_lahir" class="{{ $label }}">Tanggal Lahir <span
                class="text-red-500">*</span></label>
        <input id="tanggal_lahir" name="tanggal_lahir" type="date"
            value="{{ old('tanggal_lahir', optional($pegawai->tanggal_lahir)->format('Y-m-d')) }}"
            class="{{ $input }}" required>
        @error('tanggal_lahir')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="jenis_kelamin" class="{{ $label }}">Jenis Kelamin <span
                class="text-red-500">*</span></label>
        <select id="jenis_kelamin" name="jenis_kelamin" class="{{ $input }}" required>
            <option value="">-- Pilih --</option>
            <option value="L" @selected($field('jenis_kelamin') === 'L')>Laki-laki</option>
            <option value="P" @selected($field('jenis_kelamin') === 'P')>Perempuan</option>
        </select>
        @error('jenis_kelamin')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="status" class="{{ $label }}">Status <span class="text-red-500">*</span></label>
        <select id="status" name="status" class="{{ $input }}" required>
            @foreach (['aktif' => 'Aktif', 'pensiun' => 'Pensiun', 'pindah' => 'Pindah'] as $value => $text)
                <option value="{{ $value }}" @selected($field('status', 'aktif') === $value)>{{ $text }}</option>
            @endforeach
        </select>
        @error('status')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="pangkat_golongan" class="{{ $label }}">Pangkat / Golongan</label>
        <input id="pangkat_golongan" name="pangkat_golongan" type="text" value="{{ $field('pangkat_golongan') }}"
            placeholder="contoh: III/b" class="{{ $input }}">
        @error('pangkat_golongan')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="tmt_cpns" class="{{ $label }}">TMT CPNS</label>
        <input id="tmt_cpns" name="tmt_cpns" type="date"
            value="{{ old('tmt_cpns', optional($pegawai->tmt_cpns)->format('Y-m-d')) }}" class="{{ $input }}">
        @error('tmt_cpns')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="jabatan" class="{{ $label }}">Jabatan</label>
        <input id="jabatan" name="jabatan" type="text" value="{{ $field('jabatan') }}"
            class="{{ $input }}">
        @error('jabatan')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="unit_kerja" class="{{ $label }}">Unit Kerja</label>
        <input id="unit_kerja" name="unit_kerja" type="text"
            value="{{ $field('unit_kerja', 'Puskesmas Sunyaragi') }}" class="{{ $input }}">
        @error('unit_kerja')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="no_hp" class="{{ $label }}">No. HP</label>
        <input id="no_hp" name="no_hp" type="text" value="{{ $field('no_hp') }}" class="{{ $input }}">
        @error('no_hp')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="email" class="{{ $label }}">Email</label>
        <input id="email" name="email" type="email" value="{{ $field('email') }}"
            class="{{ $input }}">
        @error('email')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>
</div>

{{-- Isi otomatis tanggal lahir, jenis kelamin, TMT CPNS dari NIP (hanya jika masih kosong) --}}
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const nip = document.getElementById('nip');
        nip.addEventListener('input', () => {
            nip.value = nip.value.replace(/\D/g, '').slice(0, 18);
            if (nip.value.length === 18) {
                const v = nip.value;
                const tgl = document.getElementById('tanggal_lahir');
                const jk = document.getElementById('jenis_kelamin');
                const tmt = document.getElementById('tmt_cpns');
                if (!tgl.value) tgl.value = `${v.slice(0, 4)}-${v.slice(4, 6)}-${v.slice(6, 8)}`;
                if (!jk.value) jk.value = v[14] === '1' ? 'L' : (v[14] === '2' ? 'P' : '');
                if (!tmt.value) {
                    const bln = parseInt(v.slice(12, 14), 10);
                    // PNS: digit 13-14 = bulan (01-12). PPPK (kode 21/22): pakai Januari.
                    const bulan = (bln >= 1 && bln <= 12) ? v.slice(12, 14) : '01';
                    tmt.value = `${v.slice(8, 12)}-${bulan}-01`;
                }
            }
        });
    });
</script>
