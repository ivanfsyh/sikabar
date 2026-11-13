<?php

namespace App\Services;

use App\Models\Pegawai;
use Carbon\Carbon;

class PesanBuilder
{
    public static function variabel(): array
    {
        return [
            '{nama}' => 'Nama pegawai',
            '{nip}' => 'NIP',
            '{golongan}' => 'Pangkat/golongan',
            '{jabatan}' => 'Jabatan',
            '{unit_kerja}' => 'Unit kerja',
            '{tmt_kgb_berikutnya}' => 'Tanggal KGB berikutnya',
            '{tmt_kgb_terakhir}' => 'Tanggal KGB terakhir',
            '{sisa_hari}' => 'Sisa waktu (mis. "7 hari lagi")',
        ];
    }

    /** Peta variabel → nilai untuk seorang pegawai. */
    public static function nilai(Pegawai $pegawai, Carbon $jatuhTempo): array
    {
        $selisih = (int) today()->diffInDays($jatuhTempo->copy()->startOfDay(), false);

        $sisa = match (true) {
            $selisih > 0 => $selisih . ' hari lagi',
            $selisih === 0 => 'hari ini',
            default => 'terlambat ' . abs($selisih) . ' hari',
        };

        $terakhir = $pegawai->kgbTerbaru?->tmt_kgb;

        return [
            '{nama}' => $pegawai->nama,
            '{nip}' => $pegawai->nip,
            '{golongan}' => $pegawai->pangkat_golongan ?? '-',
            '{jabatan}' => $pegawai->jabatan ?? '-',
            '{unit_kerja}' => $pegawai->unit_kerja ?? '-',
            '{tmt_kgb_berikutnya}' => $jatuhTempo->translatedFormat('d F Y'),
            '{tmt_kgb_terakhir}' => $terakhir ? $terakhir->translatedFormat('d F Y') : '-',
            '{sisa_hari}' => $sisa,
        ];
    }

    /** Data contoh untuk pesan uji. */
    public static function nilaiContoh(): array
    {
        $tgl = today()->addDays(7);

        return [
            '{nama}' => 'Budi Santoso, S.Kep',
            '{nip}' => '198501152010011001',
            '{golongan}' => 'III/b',
            '{jabatan}' => 'Perawat Ahli Muda',
            '{unit_kerja}' => 'Puskesmas Sunyaragi',
            '{tmt_kgb_berikutnya}' => $tgl->translatedFormat('d F Y'),
            '{tmt_kgb_terakhir}' => $tgl->copy()->subYears(2)->translatedFormat('d F Y'),
            '{sisa_hari}' => '7 hari lagi',
        ];
    }

    public static function isi(string $teks, array $nilai): string
    {
        return strtr($teks, $nilai);
    }

    /**
     * Ubah definisi parameter template WhatsApp menjadi nilai siap kirim.
     * KirimDev menolak parameter yang kosong, berisi baris baru/tab, atau spasi beruntun,
     * jadi nilai dirapikan dan diganti "-" jika kosong.
     *
     * @param  array|null $definisi daftar ['nama' => ?string, 'nilai' => string]
     * @return array daftar ['name' => ?string, 'text' => string]
     */
    public static function parameterWa(?array $definisi, array $nilai): array
    {
        $hasil = [];

        foreach ($definisi ?? [] as $p) {
            $teks = trim(preg_replace('/\s+/', ' ', strtr((string) ($p['nilai'] ?? ''), $nilai)));

            $hasil[] = [
                'name' => filled($p['nama'] ?? null) ? $p['nama'] : null,
                'text' => $teks !== '' ? $teks : '-',
            ];
        }

        return $hasil;
    }
}
