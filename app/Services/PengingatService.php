<?php

namespace App\Services;

use App\Jobs\KirimPengingatJob;
use App\Models\AturanPengingat;
use App\Models\Pegawai;
use App\Models\RiwayatPengingat;
use App\Models\Setting;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;

class PengingatService
{
    /** Buat catatan pengingat yang jatuh tempo hari ini & antrikan pengirimannya. Return: jumlah dijadwalkan. */
    public function proses(): int
    {
        $hariIni = today();
        $toleransi = max(0, (int) Setting::get('toleransi_hari', 2));

        $aturan = AturanPengingat::with('template')
            ->where('aktif', true)
            ->get()
            ->filter(fn($a) => $a->template);

        if ($aturan->isEmpty()) {
            return 0;
        }

        $dibuat = 0;

        Pegawai::aktif()->with('kgbTerbaru')->chunkById(100, function ($pegawais) use ($aturan, $hariIni, $toleransi, &$dibuat) {
            foreach ($pegawais as $pegawai) {
                $kgb = $pegawai->kgbTerbaru;
                if (! $kgb) {
                    continue;
                }

                $jatuhTempo = $kgb->tmt_kgb_berikutnya->copy()->startOfDay();
                $nilai = PesanBuilder::nilai($pegawai, $jatuhTempo);

                foreach ($aturan as $a) {
                    $tglKirim = $jatuhTempo->copy()->subDays($a->hari_sebelum);

                    // Hanya yang jatuh hari ini, atau terlewat paling lama $toleransi hari
                    if ($tglKirim->gt($hariIni) || $tglKirim->lt($hariIni->copy()->subDays($toleransi))) {
                        continue;
                    }

                    try {
                        $log = RiwayatPengingat::firstOrCreate(
                            [
                                'pegawai_id' => $pegawai->id,
                                'aturan_pengingat_id' => $a->id,
                                'tmt_jatuh_tempo' => $jatuhTempo->toDateString(),
                            ],
                            [
                                'nama_aturan' => $a->nama,
                                'status' => 'menunggu',
                                'subjek' => PesanBuilder::isi($a->template->subjek, $nilai),
                                'pesan' => PesanBuilder::isi($a->template->isi, $nilai),
                                'wa_template' => $a->template->wa_template,
                                'wa_bahasa' => $a->template->wa_bahasa ?: 'id',
                                'wa_params' => PesanBuilder::parameterWa($a->template->wa_params, $nilai),
                                'idempotency_key' => (string) Str::uuid(),
                            ]
                        );
                    } catch (QueryException) {
                        continue; // sudah dibuat proses lain (kunci unik)
                    }

                    if ($log->wasRecentlyCreated) {
                        KirimPengingatJob::dispatch($log->id);
                        $dibuat++;
                    }
                }
            }
        });

        return $dibuat;
    }
}
