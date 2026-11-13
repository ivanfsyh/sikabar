<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class FonnteService
{
    public const BASE_URL = 'https://api.fonnte.com';

    /** Fonnte dipilih sebagai provider WhatsApp dan token sudah diisi. */
    public function aktif(): bool
    {
        return Setting::waProvider() === 'fonnte' && $this->terkonfigurasi();
    }

    private function terkonfigurasi(): bool
    {
        return (bool) Setting::get('fonnte_token');
    }

    /**
     * Kirim pesan teks WhatsApp lewat Fonnte.
     *
     * @return array ['ok' => bool, 'pesan' => string, 'respon' => ?string]
     */
    public function kirim(string $nomor, string $pesan): array
    {
        $normal = KirimDevService::normalisasiNomor($nomor);

        if (! $normal) {
            return ['ok' => false, 'pesan' => 'Nomor HP tidak valid.', 'respon' => null];
        }

        if (! $this->terkonfigurasi()) {
            return ['ok' => false, 'pesan' => 'Token Fonnte belum diisi.', 'respon' => null];
        }

        try {
            $res = $this->http()->post('/send', [
                'target' => $normal,
                'message' => $pesan,
                'countryCode' => '62',
            ]);
        } catch (\Throwable $e) {
            return ['ok' => false, 'pesan' => 'Tidak dapat terhubung ke Fonnte: ' . Str::limit($e->getMessage(), 200), 'respon' => null];
        }

        // Fonnte membalas {"status": true, ...} jika diterima, {"status": false, "reason": "..."} jika ditolak
        if ($res->successful() && $res->json('status') === true) {
            return ['ok' => true, 'pesan' => 'Diterima Fonnte.', 'respon' => Str::limit($res->body(), 1000)];
        }

        $detail = $res->json('reason') ?? $res->json('detail') ?? $res->json('message') ?? Str::limit($res->body(), 300);

        return ['ok' => false, 'pesan' => 'HTTP ' . $res->status() . ': ' . Str::limit((string) $detail, 300), 'respon' => Str::limit($res->body(), 1000)];
    }

    /**
     * Cek token dan status device (apakah nomor WhatsApp sedang terhubung).
     *
     * @return array ['ok' => bool, 'pesan' => string]
     */
    public function cekKoneksi(): array
    {
        if (! $this->terkonfigurasi()) {
            return ['ok' => false, 'pesan' => 'Token Fonnte belum diisi.'];
        }

        try {
            $res = $this->http()->post('/device');
        } catch (\Throwable $e) {
            return ['ok' => false, 'pesan' => 'Tidak dapat terhubung ke Fonnte: ' . Str::limit($e->getMessage(), 200)];
        }

        if (! $res->successful() || $res->json('status') !== true) {
            $alasan = $res->json('reason') ?? Str::limit($res->body(), 200);

            return ['ok' => false, 'pesan' => 'Token ditolak Fonnte: ' . $alasan];
        }

        $status = strtolower((string) $res->json('device_status'));
        $nomor = $res->json('device');

        if ($status === 'connect') {
            return ['ok' => true, 'pesan' => 'Fonnte terhubung' . ($nomor ? ' (nomor ' . $nomor . ')' : '') . '.'];
        }

        return [
            'ok' => false,
            'pesan' => 'Token valid, tetapi device Fonnte belum terhubung (status: ' . ($status ?: 'tidak diketahui') . '). Scan QR di dashboard Fonnte.',
        ];
    }

    private function http(): PendingRequest
    {
        // Fonnte memakai token langsung di header Authorization (tanpa "Bearer")
        return Http::baseUrl(self::BASE_URL)
            ->withHeaders(['Authorization' => (string) Setting::get('fonnte_token')])
            ->acceptJson()
            ->asForm()
            ->timeout(20);
    }
}
