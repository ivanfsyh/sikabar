@extends('layouts.app')

@section('title', 'Integrasi & Jadwal')
@section('page-title', 'Integrasi & Jadwal')

@php
    use App\Models\Setting;

    $input =
        'mt-1.5 w-full rounded-xl border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 placeholder-slate-300 focus:border-blue-500 focus:ring-blue-500';
    $label = 'block text-sm font-medium text-slate-600';
    $v = fn($key, $default = null) => old($key, Setting::get($key, $default));
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

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-800">Integrasi & Jadwal</h1>
        <p class="mt-1 text-sm text-slate-400">Pilih satu kanal WhatsApp (KirimDev atau Fonnte). Email bisa dipakai sebagai
            cadangan jika WhatsApp gagal.</p>
    </div>

    <form method="POST" action="{{ route('pengaturan.integrasi.simpan') }}" class="space-y-6" x-data="{ provider: '{{ old('wa_provider', Setting::waProvider()) }}' }">
        @csrf
        @method('PUT')

        {{-- PILIH KANAL WHATSAPP --}}
        <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
            <h3 class="font-semibold text-slate-800">Kanal WhatsApp</h3>
            <p class="mt-1 text-xs text-slate-400">Hanya satu yang aktif pada satu waktu.</p>

            <div class="mt-4 grid gap-3 md:grid-cols-3">
                @foreach (['kirimdev' => ['KirimDev', 'Template WhatsApp Business yang sudah disetujui'], 'fonnte' => ['Fonnte', 'Pesan teks bebas lewat nomor WhatsApp'], 'none' => ['Nonaktif', 'Tidak memakai WhatsApp (hanya email)']] as $val => [$judul, $ket])
                    <label class="flex cursor-pointer gap-3 rounded-xl border p-4"
                        :class="provider === '{{ $val }}' ? 'border-blue-500 bg-blue-50/50' : 'border-slate-200'">
                        <input type="radio" name="wa_provider" value="{{ $val }}" x-model="provider"
                            class="mt-1 text-blue-600">
                        <span>
                            <span class="block text-sm font-semibold text-slate-700">{{ $judul }}</span>
                            <span class="block text-xs text-slate-400">{{ $ket }}</span>
                        </span>
                    </label>
                @endforeach
            </div>
        </div>

        {{-- KIRIMDEV --}}
        <div x-show="provider === 'kirimdev'" class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
            <h3 class="font-semibold text-slate-800">WhatsApp (KirimDev)</h3>
            <p class="mt-1 text-xs text-slate-400">
                Pesan proaktif wajib memakai template WhatsApp yang sudah disetujui. Buat dan ajukan template di dashboard
                KirimDev,
                lalu petakan di menu <b>Template Pesan</b>.
            </p>

            <div class="mt-5 grid gap-5 md:grid-cols-2">
                <div class="md:col-span-2">
                    <label class="{{ $label }}">Base URL API</label>
                    <input name="kirim_base_url"
                        value="{{ $v('kirim_base_url', \App\Services\KirimDevService::BASE_URL_DEFAULT) }}"
                        class="{{ $input }}">
                </div>
                <div>
                    <label class="{{ $label }}">API Key</label>
                    <input type="password" name="kirim_api_key" autocomplete="new-password"
                        placeholder="{{ $adaApiKey ? '•••••••• (tersimpan, kosongkan jika tidak diubah)' : 'kdv_live_...' }}"
                        class="{{ $input }}">
                    <p class="mt-1 text-xs text-slate-400">Dibuat di dashboard KirimDev, menu Settings → API Keys.</p>
                </div>
                <div>
                    <label class="{{ $label }}">Phone Number ID</label>
                    <input name="kirim_phone_id" value="{{ $v('kirim_phone_id') }}" placeholder="106540352242922"
                        class="{{ $input }}">
                    <p class="mt-1 text-xs text-slate-400">ID nomor WhatsApp pengirim di akun KirimDev.</p>
                </div>
            </div>
        </div>

        {{-- FONNTE --}}
        <div x-show="provider === 'fonnte'" class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
            <h3 class="font-semibold text-slate-800">WhatsApp (Fonnte)</h3>
            <p class="mt-1 text-xs text-slate-400">
                Fonnte mengirim teks bebas dari kolom <b>Isi pesan</b> di menu <b>Template Pesan</b>. Pastikan device
                (nomor WhatsApp) sudah terhubung di dashboard Fonnte.
            </p>

            <div class="mt-5">
                <label class="{{ $label }}">Token Device</label>
                <input type="password" name="fonnte_token" autocomplete="new-password"
                    placeholder="{{ $adaFonnteToken ? '•••••••• (tersimpan, kosongkan jika tidak diubah)' : 'Token dari dashboard Fonnte' }}"
                    class="{{ $input }}">
                <p class="mt-1 text-xs text-slate-400">Ada di dashboard Fonnte, menu Device → token pada device yang
                    dipakai.
                </p>
            </div>
        </div>

        {{-- EMAIL --}}
        <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <h3 class="font-semibold text-slate-800">Email Cadangan</h3>
                <label class="flex items-center gap-2 text-sm text-slate-600">
                    <input type="hidden" name="email_aktif" value="0">
                    <input type="checkbox" name="email_aktif" value="1" @checked(old('email_aktif', Setting::bool('email_aktif')))
                        class="rounded border-slate-300 text-blue-600">
                    Aktif
                </label>
            </div>
            <p class="mt-1 text-xs text-slate-400">Dikirim jika WhatsApp gagal/tidak terhubung, WhatsApp nonaktif, atau
                nomor HP pegawai kosong.</p>

            <div class="mt-5 grid gap-5 md:grid-cols-2">
                <div>
                    <label class="{{ $label }}">Email Pengirim</label>
                    <input type="email" name="email_dari_alamat" value="{{ $v('email_dari_alamat') }}"
                        placeholder="noreply@domain-anda.com" class="{{ $input }}">
                </div>
                <div>
                    <label class="{{ $label }}">Nama Pengirim</label>
                    <input name="email_dari_nama" value="{{ $v('email_dari_nama', 'SIKABAR') }}"
                        class="{{ $input }}">
                </div>
            </div>

            <p class="mb-1 mt-6 text-sm font-medium text-slate-600">Server SMTP <span
                    class="font-normal text-slate-400">(kosongkan host untuk memakai pengaturan .env)</span></p>
            <div class="grid gap-5 md:grid-cols-2">
                <div>
                    <label class="{{ $label }}">SMTP Host</label>
                    <input name="smtp_host" value="{{ $v('smtp_host') }}" placeholder="mail.domain-anda.com"
                        class="{{ $input }}">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="{{ $label }}">Port</label>
                        <input type="number" name="smtp_port" value="{{ $v('smtp_port', 587) }}"
                            class="{{ $input }}">
                    </div>
                    <div>
                        <label class="{{ $label }}">Enkripsi</label>
                        <select name="smtp_enkripsi" class="{{ $input }}">
                            @foreach (['tls' => 'TLS (587)', 'ssl' => 'SSL (465)', 'none' => 'Tanpa'] as $val => $teks)
                                <option value="{{ $val }}" @selected($v('smtp_enkripsi', 'tls') === $val)>{{ $teks }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div>
                    <label class="{{ $label }}">Username</label>
                    <input name="smtp_username" value="{{ $v('smtp_username') }}" autocomplete="off"
                        class="{{ $input }}">
                </div>
                <div>
                    <label class="{{ $label }}">Password</label>
                    <input type="password" name="smtp_password" autocomplete="new-password"
                        placeholder="{{ $adaSmtpPassword ? '•••••••• (tersimpan, kosongkan jika tidak diubah)' : '' }}"
                        class="{{ $input }}">
                </div>
            </div>
        </div>

        {{-- JADWAL --}}
        <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
            <h3 class="font-semibold text-slate-800">Jadwal Pengiriman</h3>
            <div class="mt-5 grid gap-5 md:grid-cols-2">
                <div>
                    <label class="{{ $label }}">Jam proses harian</label>
                    <input type="time" name="jam_kirim" value="{{ $v('jam_kirim', '08:00') }}"
                        class="{{ $input }}" required>
                    <p class="mt-1 text-xs text-slate-400">Sistem memeriksa pegawai yang perlu diingatkan pada jam ini
                        setiap hari.</p>
                </div>
                <div>
                    <label class="{{ $label }}">Toleransi keterlambatan (hari)</label>
                    <input type="number" name="toleransi_hari" min="0" max="14"
                        value="{{ $v('toleransi_hari', 2) }}" class="{{ $input }}" required>
                    <p class="mt-1 text-xs text-slate-400">Jika proses harian terlewat, pengingat masih dikirim selama
                        belum melewati batas ini.</p>
                </div>
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit"
                class="rounded-xl bg-blue-600 px-8 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">Simpan
                Pengaturan</button>
        </div>
    </form>

    {{-- UJI KONEKSI (form terpisah) --}}
    <div class="mt-8 rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
        <h3 class="font-semibold text-slate-800">Uji Koneksi</h3>
        <p class="mt-1 text-xs text-slate-400">Simpan pengaturan terlebih dulu sebelum menguji. Pesan uji memakai data
            contoh dan dapat menggunakan kuota/biaya template WhatsApp.</p>

        <div class="mt-5 grid gap-6 lg:grid-cols-3">
            <form method="POST" action="{{ route('pengaturan.kirim.tes') }}" class="flex flex-col justify-end">
                @csrf
                <p class="mb-2 text-sm text-slate-600">Cek API key dan daftar template</p>
                <button
                    class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-50">Cek
                    Koneksi</button>
            </form>

            <form method="POST" action="{{ route('pengaturan.kirim.uji') }}" class="space-y-2">
                @csrf
                <label class="{{ $label }}">Kirim WhatsApp uji</label>
                <select name="template_pesan_id"
                    class="w-full rounded-xl border-slate-200 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-blue-500">
                    @forelse ($templates as $t)
                        <option value="{{ $t->id }}">{{ $t->nama }} ({{ $t->wa_template }})</option>
                    @empty
                        <option value="">Belum ada template berisi nama template WhatsApp</option>
                    @endforelse
                </select>
                <div class="flex gap-2">
                    <input name="nomor" placeholder="08123456789"
                        class="w-full rounded-xl border-slate-200 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-blue-500">
                    <button
                        class="rounded-xl bg-slate-800 px-4 py-2.5 text-sm font-medium text-white hover:bg-slate-700">Kirim</button>
                </div>
            </form>

            <form method="POST" action="{{ route('pengaturan.email.uji') }}">
                @csrf
                <label class="{{ $label }}">Kirim email uji ke alamat</label>
                <div class="mt-1.5 flex gap-2">
                    <input type="email" name="email" placeholder="nama@email.com"
                        class="w-full rounded-xl border-slate-200 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-blue-500">
                    <button
                        class="rounded-xl bg-slate-800 px-4 py-2.5 text-sm font-medium text-white hover:bg-slate-700">Kirim</button>
                </div>
            </form>
        </div>

        {{-- UJI FONNTE --}}
        <div class="mt-6 grid gap-6 border-t border-slate-100 pt-6 lg:grid-cols-3">
            <form method="POST" action="{{ route('pengaturan.fonnte.tes') }}" class="flex flex-col justify-end">
                @csrf
                <p class="mb-2 text-sm text-slate-600">Fonnte: cek token dan status device</p>
                <button
                    class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-50">Cek
                    Koneksi Fonnte</button>
            </form>

            <form method="POST" action="{{ route('pengaturan.fonnte.uji') }}">
                @csrf
                <label class="{{ $label }}">Kirim WhatsApp uji (Fonnte)</label>
                <div class="mt-1.5 flex gap-2">
                    <input name="nomor_fonnte" placeholder="08123456789"
                        class="w-full rounded-xl border-slate-200 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-blue-500">
                    <button
                        class="rounded-xl bg-slate-800 px-4 py-2.5 text-sm font-medium text-white hover:bg-slate-700">Kirim</button>
                </div>
            </form>
        </div>

        {{-- HASIL CEK KONEKSI --}}
        @php
            $k = session('koneksi');

            $badge = fn(?string $s) => match (true) {
                $s === 'approved' => ['Aktif', 'bg-emerald-50 text-emerald-600'],
                in_array($s, ['pending', 'in_review'], true) => ['Menunggu persetujuan', 'bg-amber-50 text-amber-600'],
                $s === null => ['Tidak ditemukan', 'bg-red-50 text-red-600'],
                default => [ucfirst($s), 'bg-red-50 text-red-600'],
            };
        @endphp

        @if ($k)
            @php
                $totalTemplate = count($k['templates']);
                $templateAktif = collect($k['templates'])->where('status', 'approved')->count();
            @endphp

            <div class="mt-6 rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
                <h3 class="font-semibold text-slate-800">Hasil Pengecekan</h3>

                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div class="rounded-xl p-4 {{ $k['ok'] ? 'bg-emerald-50' : 'bg-red-50' }}">
                        <p class="text-xs text-slate-500">API Key & Koneksi</p>
                        <p class="mt-1 font-semibold {{ $k['ok'] ? 'text-emerald-600' : 'text-red-600' }}">
                            ● {{ $k['ok'] ? 'Aktif' : 'Tidak aktif' }}
                        </p>
                        @unless ($k['ok'])
                            <p class="mt-1 text-xs text-red-600">{{ $k['pesan'] }}</p>
                        @endunless
                    </div>

                    <div class="rounded-xl p-4 {{ $templateAktif > 0 ? 'bg-emerald-50' : 'bg-amber-50' }}">
                        <p class="text-xs text-slate-500">Template di KirimDev</p>
                        <p class="mt-1 font-semibold {{ $templateAktif > 0 ? 'text-emerald-600' : 'text-amber-600' }}">
                            ● {{ $templateAktif > 0 ? 'Aktif' : 'Belum ada yang aktif' }}
                            <span class="font-normal text-slate-500">({{ $templateAktif }} disetujui dari
                                {{ $totalTemplate }})</span>
                        </p>
                    </div>
                </div>

                {{-- Daftar template di KirimDev --}}
                @if ($totalTemplate > 0)
                    <p class="mb-2 mt-6 text-sm font-medium text-slate-600">Template di akun KirimDev</p>
                    <div class="overflow-x-auto rounded-xl border border-slate-100">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-400">
                                <tr>
                                    <th class="px-4 py-2.5 font-semibold">Nama</th>
                                    <th class="px-4 py-2.5 font-semibold">Bahasa</th>
                                    <th class="px-4 py-2.5 font-semibold">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @foreach ($k['templates'] as $t)
                                    @php [$teks, $warna] = $badge($t['status']); @endphp
                                    <tr>
                                        <td class="px-4 py-2.5 font-medium text-slate-700">{{ $t['name'] }}</td>
                                        <td class="px-4 py-2.5 text-slate-600">{{ $t['language'] ?: '-' }}</td>
                                        <td class="px-4 py-2.5">
                                            <span
                                                class="rounded-full px-2.5 py-1 text-xs font-medium {{ $warna }}">{{ $teks }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @elseif ($k['ok'])
                    <p class="mt-6 text-sm text-amber-600">Koneksi berhasil, tetapi belum ada template di akun ini. Buat
                        dan ajukan template di dashboard KirimDev.</p>
                @endif

                {{-- Pemetaan template di aplikasi --}}
                @if ($k['ok'])
                    <p class="mb-2 mt-6 text-sm font-medium text-slate-600">Template WhatsApp yang dipakai di aplikasi</p>

                    @forelse ($k['pemetaan'] as $p)
                        @php [$teks, $warna] = $badge($p['status']); @endphp
                        <div
                            class="mb-2 flex flex-wrap items-center justify-between gap-2 rounded-xl border border-slate-100 p-3">
                            <div>
                                <p class="text-sm font-semibold text-slate-700">{{ $p['nama'] }}</p>
                                <p class="text-xs text-slate-400">
                                    <code>{{ $p['wa_template'] }}</code> · bahasa <code>{{ $p['bahasa'] }}</code>
                                </p>
                                @if ($p['status'] !== 'approved')
                                    <p class="mt-1 text-xs text-red-500">
                                        @if ($p['status'] === null && count($p['bahasa_tersedia']))
                                            Nama template ada, tetapi bahasa yang tersedia:
                                            <b>{{ implode(', ', $p['bahasa_tersedia']) }}</b>.
                                            Ubah kolom Bahasa di menu Template Pesan.
                                        @elseif ($p['status'] === null)
                                            Nama template ini tidak ada di akun KirimDev. Periksa penulisannya di menu
                                            Template Pesan.
                                        @else
                                            Template belum disetujui, pesan belum bisa dikirim.
                                        @endif
                                    </p>
                                @endif
                            </div>
                            <span
                                class="rounded-full px-2.5 py-1 text-xs font-medium {{ $warna }}">{{ $teks }}</span>
                        </div>
                    @empty
                        <p class="text-sm text-slate-400">Belum ada template pesan yang berisi nama template WhatsApp.</p>
                    @endforelse
                @endif
            </div>
        @endif
    </div>
@endsection
