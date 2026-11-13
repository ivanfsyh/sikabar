<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class KirimDevService
{
    public const BASE_URL_DEFAULT = 'https://api.kirimdev.com/v1';

    public function aktif(): bool
    {
        return Setting::bool('kirim_aktif')
            && Setting::get('kirim_api_key')
            && Setting::get('kirim_phone_id');
    }

    private function terkonfigurasi(): bool
    {
        return (bool) (Setting::get('kirim_api_key') && Setting::get('kirim_phone_id'));
    }

    /** 0812xxx / 812xxx / +62812xxx  →  62812xxx. Null jika tidak valid. */
    public static function normalisasiNomor(?string $nomor): ?string
    {
        $digit = preg_replace('/\D+/', '', (string) $nomor);

        if ($digit === '') {
            return null;
        }

        if (str_starts_with($digit, '0')) {
            $digit = '62' . substr($digit, 1);
        } elseif (str_starts_with($digit, '8')) {
            $digit = '62' . $digit;
        }

        return (strlen($digit) >= 10 && strlen($digit) <= 15) ? $digit : null;
    }

    /**
     * Kirim pesan template WhatsApp.
     *
     * @param array $params daftar ['name' => ?string, 'text' => string]; 'name' diisi untuk parameter bernama
     */
    public function kirimTemplate(string $nomor, string $template, string $bahasa, array $params, ?string $kunci = null): array
    {
        $normal = self::normalisasiNomor($nomor);

        if (! $normal) {
            return ['ok' => false, 'pesan' => 'Nomor HP tidak valid.', 'respon' => null];
        }

        if (! $this->terkonfigurasi()) {
            return ['ok' => false, 'pesan' => 'API key atau Phone Number ID KirimDev belum diisi.', 'respon' => null];
        }

        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => '+' . $normal,
            'type' => 'template',
            'template' => [
                'name' => $template,
                'language' => $bahasa ?: 'id',
            ],
        ];

        if (! empty($params)) {
            $payload['template']['components'] = [[
                'type' => 'body',
                'parameters' => array_map(function ($p) {
                    $row = ['type' => 'text', 'text' => $p['text']];
                    if (! empty($p['name'])) {
                        $row['parameter_name'] = $p['name'];
                    }

                    return $row;
                }, $params),
            ]];
        }

        try {
            $http = $this->http();
            if ($kunci) {
                $http = $http->withHeaders(['Idempotency-Key' => $kunci]);
            }

            $res = $http->post('/' . Setting::get('kirim_phone_id') . '/messages', $payload);
        } catch (\Throwable $e) {
            return ['ok' => false, 'pesan' => 'Tidak dapat terhubung ke KirimDev: ' . Str::limit($e->getMessage(), 200), 'respon' => null];
        }

        if ($res->successful()) {
            return ['ok' => true, 'pesan' => 'Diterima KirimDev.', 'respon' => Str::limit($res->body(), 1000)];
        }

        $detail = $res->json('error.message') ?? $res->json('message') ?? Str::limit($res->body(), 300);

        return ['ok' => false, 'pesan' => 'HTTP ' . $res->status() . ': ' . Str::limit((string) $detail, 300), 'respon' => Str::limit($res->body(), 1000)];
    }

    /** Cek koneksi: ambil daftar template yang tersedia di akun. */
    public function cekKoneksi(): array
    {
        if (! $this->terkonfigurasi()) {
            return ['ok' => false, 'pesan' => 'API key atau Phone Number ID KirimDev belum diisi.', 'templates' => []];
        }

        try {
            $res = $this->http()->get('/' . Setting::get('kirim_phone_id') . '/templates');
        } catch (\Throwable $e) {
            return ['ok' => false, 'pesan' => 'Tidak dapat terhubung ke KirimDev: ' . Str::limit($e->getMessage(), 200), 'templates' => []];
        }

        if (in_array($res->status(), [401, 403], true)) {
            return ['ok' => false, 'pesan' => 'API key ditolak (HTTP ' . $res->status() . '). Periksa API key dan Phone Number ID.', 'templates' => []];
        }

        if (! $res->successful()) {
            return ['ok' => false, 'pesan' => 'HTTP ' . $res->status() . ': ' . Str::limit($res->body(), 200), 'templates' => []];
        }

        // Bentuk respons bisa {data:[...]} atau array langsung
        $items = $res->json('data');
        if (! is_array($items)) {
            $items = $res->json();
        }

        $templates = collect(is_array($items) ? $items : [])
            ->filter(fn($t) => is_array($t) && isset($t['name']))
            ->map(function ($t) {
                $bahasa = $t['language'] ?? '';
                if (is_array($bahasa)) {
                    $bahasa = $bahasa['code'] ?? '';
                }

                return [
                    'name' => (string) $t['name'],
                    'language' => (string) $bahasa,
                    'status' => strtolower((string) ($t['status'] ?? 'unknown')),
                ];
            })
            ->values()
            ->all();

        return ['ok' => true, 'pesan' => 'Koneksi ke KirimDev berhasil.', 'templates' => $templates];
    }
    private function http(): PendingRequest
    {
        $base = rtrim((string) Setting::get('kirim_base_url', self::BASE_URL_DEFAULT), '/');

        return Http::baseUrl($base)
            ->withToken((string) Setting::get('kirim_api_key'))
            ->acceptJson()
            ->asJson()
            ->timeout(20);
    }
}
