<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\TemplatePesan;
use App\Services\KirimDevService;
use App\Services\PengaturanMail;
use App\Services\PesanBuilder;
use App\Services\FonnteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PengaturanController extends Controller
{
    public function integrasi()
    {
        return view('pengaturan.integrasi', [
            'adaApiKey' => (bool) Setting::get('kirim_api_key'),
            'adaFonnteToken' => (bool) Setting::get('fonnte_token'),
            'adaSmtpPassword' => (bool) Setting::get('smtp_password'),
            'templates' => TemplatePesan::whereNotNull('wa_template')->orderBy('nama')->get(),
        ]);
    }

    public function simpan(Request $request)
    {
        $data = $request->validate([
            'wa_provider' => ['required', Rule::in(['kirimdev', 'fonnte', 'none'])],
            'kirim_base_url' => ['nullable', 'url', 'max:255'],
            'kirim_api_key' => [
                'nullable',
                'string',
                'max:500',
                Rule::requiredIf(fn() => $request->input('wa_provider') === 'kirimdev' && ! Setting::get('kirim_api_key')),
            ],
            'kirim_phone_id' => ['nullable', 'string', 'max:50', 'required_if:wa_provider,kirimdev'],
            'fonnte_token' => [
                'nullable',
                'string',
                'max:500',
                Rule::requiredIf(fn() => $request->input('wa_provider') === 'fonnte' && ! Setting::get('fonnte_token')),
            ],
            'email_dari_alamat' => ['nullable', 'email', 'max:255', 'required_if:email_aktif,1'],
            'email_dari_nama' => ['nullable', 'string', 'max:100'],
            'smtp_host' => ['nullable', 'string', 'max:255'],
            'smtp_port' => ['nullable', 'integer', 'between:1,65535'],
            'smtp_username' => ['nullable', 'string', 'max:255'],
            'smtp_password' => ['nullable', 'string', 'max:255'],
            'smtp_enkripsi' => ['nullable', Rule::in(['tls', 'ssl', 'none'])],
            'jam_kirim' => ['required', 'date_format:H:i'],
            'toleransi_hari' => ['required', 'integer', 'between:0,14'],
        ], [
            'kirim_base_url.url' => 'Base URL tidak valid (contoh: https://api.kirimdev.com/v1).',
            'wa_provider.required' => 'Pilih kanal WhatsApp (KirimDev, Fonnte, atau Nonaktif).',
            'kirim_api_key.required' => 'API key KirimDev wajib diisi jika KirimDev dipilih.',
            'kirim_phone_id.required_if' => 'Phone Number ID wajib diisi jika KirimDev dipilih.',
            'fonnte_token.required' => 'Token Fonnte wajib diisi jika Fonnte dipilih.',
            'email_dari_alamat.required_if' => 'Email pengirim wajib diisi jika email cadangan diaktifkan.',
            'jam_kirim.date_format' => 'Format jam harus HH:MM (contoh 08:00).',
        ]);

        foreach (
            [
                'kirim_base_url',
                'kirim_phone_id',
                'email_dari_alamat',
                'email_dari_nama',
                'smtp_host',
                'smtp_port',
                'smtp_username',
                'smtp_enkripsi',
                'jam_kirim',
                'toleransi_hari',
            ] as $kunci
        ) {
            Setting::set($kunci, $data[$kunci] ?? null);
        }

        // Satu provider WhatsApp aktif pada satu waktu
        Setting::set('wa_provider', $data['wa_provider']);
        Setting::set('kirim_aktif', $data['wa_provider'] === 'kirimdev' ? '1' : '0');
        Setting::set('email_aktif', $request->boolean('email_aktif') ? '1' : '0');

        // Nilai rahasia hanya diganti jika diisi
        foreach (['kirim_api_key', 'smtp_password', 'fonnte_token'] as $kunci) {
            if (filled($data[$kunci] ?? null)) {
                Setting::set($kunci, $data[$kunci]);
            }
        }

        return back()->with('success', 'Pengaturan berhasil disimpan.');
    }

    public function tesKoneksi(KirimDevService $kirim)
    {
        $hasil = $kirim->cekKoneksi();

        // Cocokkan tiap template di aplikasi dengan daftar di KirimDev (nama + bahasa)
        $pemetaan = TemplatePesan::whereNotNull('wa_template')
            ->orderBy('nama')
            ->get()
            ->map(function ($t) use ($hasil) {
                $bahasa = $t->wa_bahasa ?: 'id';
                $daftar = collect($hasil['templates']);

                $cocok = $daftar->first(
                    fn($x) => $x['name'] === $t->wa_template && strcasecmp($x['language'], $bahasa) === 0
                );

                return [
                    'nama' => $t->nama,
                    'wa_template' => $t->wa_template,
                    'bahasa' => $bahasa,
                    'status' => $cocok['status'] ?? null,
                    // bahasa lain yang tersedia untuk nama template yang sama (petunjuk jika bahasa salah)
                    'bahasa_tersedia' => $daftar->where('name', $t->wa_template)->pluck('language')->values()->all(),
                ];
            })
            ->all();

        return back()
            ->with($hasil['ok'] ? 'success' : 'error', $hasil['ok'] ? 'Koneksi ke KirimDev berhasil.' : $hasil['pesan'])
            ->with('koneksi', $hasil + ['pemetaan' => $pemetaan]);
    }

    public function ujiKirim(Request $request, KirimDevService $kirim)
    {
        $request->validate([
            'nomor' => ['required', 'string', 'max:20'],
            'template_pesan_id' => ['required', 'exists:template_pesans,id'],
        ], [
            'nomor.required' => 'Nomor WhatsApp tujuan wajib diisi.',
            'template_pesan_id.required' => 'Pilih template pesan untuk diuji.',
        ]);

        $template = TemplatePesan::findOrFail($request->template_pesan_id);

        if (! $template->wa_template) {
            return back()->with('error', 'Template ini belum punya nama template WhatsApp.');
        }

        $params = PesanBuilder::parameterWa($template->wa_params, PesanBuilder::nilaiContoh());

        $hasil = $kirim->kirimTemplate(
            $request->nomor,
            $template->wa_template,
            $template->wa_bahasa ?: 'id',
            $params,
            (string) Str::uuid()
        );

        return back()->with(
            $hasil['ok'] ? 'success' : 'error',
            $hasil['ok'] ? 'Pesan uji diterima KirimDev. Cek WhatsApp di nomor tujuan.' : $hasil['pesan']
        );
    }

    public function tesFonnte(FonnteService $fonnte)
    {
        $hasil = $fonnte->cekKoneksi();

        return back()->with($hasil['ok'] ? 'success' : 'error', $hasil['pesan']);
    }

    public function ujiFonnte(Request $request, FonnteService $fonnte)
    {
        $request->validate(['nomor_fonnte' => ['required', 'string', 'max:20']], [
            'nomor_fonnte.required' => 'Nomor WhatsApp tujuan wajib diisi.',
        ]);

        $hasil = $fonnte->kirim(
            $request->nomor_fonnte,
            'Uji kirim WhatsApp dari SIKABAR (Fonnte). Jika Anda menerima pesan ini, integrasi berfungsi.'
        );

        return back()->with(
            $hasil['ok'] ? 'success' : 'error',
            $hasil['ok'] ? 'Pesan uji diterima Fonnte. Cek WhatsApp di nomor tujuan.' : $hasil['pesan']
        );
    }

    public function ujiEmail(Request $request)
    {
        $request->validate(['email' => ['required', 'email']], [
            'email.required' => 'Alamat email tujuan wajib diisi.',
            'email.email' => 'Format email tidak valid.',
        ]);

        try {
            PengaturanMail::terapkan();

            Mail::raw('Uji kirim email dari SIKABAR. Jika Anda menerima pesan ini, pengaturan email berfungsi.', function ($m) use ($request) {
                $m->to($request->email)->subject('Uji Email SIKABAR');
            });
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal mengirim email: ' . Str::limit($e->getMessage(), 250));
        }

        return back()->with('success', 'Email uji terkirim ke ' . $request->email . '.');
    }
}
