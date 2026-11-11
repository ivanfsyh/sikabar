<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Validasi NIP 18 digit untuk PNS dan PPPK.
 *
 *  1-8   : tanggal lahir (YYYYMMDD)
 *  9-14  : TMT pengangkatan
 *            PNS  : YYYYMM (bulan 01-12)
 *            PPPK : YYYY + kode "21" / "22"
 *  15    : jenis kelamin (1 = laki-laki, 2 = perempuan)
 *  16-18 : nomor urut
 */
class NipValid implements ValidationRule
{
    /** Kode pada digit 13-14 yang menandakan PPPK. Tambahkan di sini jika ada kode lain. */
    public const KODE_PPPK = ['21', '22'];

    public static function normalize(mixed $nip): string
    {
        return preg_replace('/[\s\.\-]+/', '', (string) $nip);
    }

    /** Jenis kepegawaian berdasarkan digit 13-14: 'PPPK', 'PNS', atau null jika tidak dikenali. */
    public static function jenis(string $nip): ?string
    {
        $nip = self::normalize($nip);
        $kode = substr($nip, 12, 2);

        if (in_array($kode, self::KODE_PPPK, true)) {
            return 'PPPK';
        }

        if ($kode >= '01' && $kode <= '12') {
            return 'PNS';
        }

        return null;
    }

    /** Mengembalikan pesan error, atau null jika NIP valid. */
    public static function check(mixed $value): ?string
    {
        $nip = self::normalize($value);

        if (! preg_match('/^\d{18}$/', $nip)) {
            return 'NIP harus 18 digit angka.';
        }

        // Tanggal lahir (digit 1-8)
        $thnLahir = (int) substr($nip, 0, 4);
        $blnLahir = (int) substr($nip, 4, 2);
        $tglLahir = (int) substr($nip, 6, 2);

        if (! checkdate($blnLahir, $tglLahir, $thnLahir) || $thnLahir < 1940 || $thnLahir > (int) date('Y')) {
            return 'NIP tidak valid: 8 digit pertama (tanggal lahir) tidak sesuai format YYYYMMDD.';
        }

        // TMT pengangkatan (digit 9-14): PNS = YYYYMM, PPPK = YYYY + kode 21/22
        $thnTmt = (int) substr($nip, 8, 4);

        if ($thnTmt < 1960 || $thnTmt > (int) date('Y') + 1) {
            return 'NIP tidak valid: tahun TMT pada digit 9-12 tidak wajar.';
        }

        if (self::jenis($nip) === null) {
            return 'NIP tidak valid: digit 13-14 harus bulan 01-12 (PNS) atau kode ' . implode('/', self::KODE_PPPK) . ' (PPPK).';
        }

        if ($thnTmt - $thnLahir < 17) {
            return 'NIP tidak valid: usia saat pengangkatan kurang dari 17 tahun.';
        }

        // Jenis kelamin (digit 15)
        if (! in_array($nip[14], ['1', '2'], true)) {
            return 'NIP tidak valid: digit ke-15 (jenis kelamin) harus 1 atau 2.';
        }

        return null;
    }

    /** Ambil data yang tertanam di NIP. */
    public static function parse(string $nip): array
    {
        $nip = self::normalize($nip);

        // PNS: tanggal 1 pada bulan TMT. PPPK: bulan tidak ada di NIP, pakai 1 Januari tahun TMT.
        $bulan = self::jenis($nip) === 'PNS' ? substr($nip, 12, 2) : '01';

        return [
            'tanggal_lahir' => substr($nip, 0, 4) . '-' . substr($nip, 4, 2) . '-' . substr($nip, 6, 2),
            'tmt_cpns' => substr($nip, 8, 4) . '-' . $bulan . '-01',
            'jenis_kelamin' => $nip[14] === '1' ? 'L' : 'P',
            'jenis' => self::jenis($nip),
        ];
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($message = self::check($value)) {
            $fail($message);
        }
    }
}
