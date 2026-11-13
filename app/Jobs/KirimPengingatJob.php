<?php

namespace App\Jobs;

use App\Models\RiwayatPengingat;
use App\Models\Setting;
use App\Services\FonnteService;
use App\Services\KirimDevService;
use App\Services\PengaturanMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

class KirimPengingatJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;
    public int $timeout = 90;

    public function __construct(public int $logId) {}

    public function handle(KirimDevService $kirim, FonnteService $fonnte): void
    {
        PengaturanMail::terapkan();

        // Kunci baris: hanya satu worker yang boleh memproses, dan hanya status "menunggu"
        $log = DB::transaction(function () {
            $l = RiwayatPengingat::lockForUpdate()->find($this->logId);

            if (! $l || $l->status !== 'menunggu') {
                return null;
            }

            $l->update(['status' => 'diproses', 'percobaan' => $l->percobaan + 1, 'error' => null]);

            return $l;
        });

        if (! $log) {
            return;
        }

        $pegawai = $log->pegawai;

        if (! $pegawai || $pegawai->trashed() || $pegawai->status !== 'aktif') {
            $log->update(['status' => 'dilewati', 'error' => 'Pegawai tidak aktif atau sudah dihapus.']);

            return;
        }

        // Jika KGB sudah diperbarui sejak pengingat dibuat, jadwal lama tidak relevan lagi
        $terbaru = $pegawai->kgbTerbaru;
        if (! $terbaru || ! $terbaru->tmt_kgb_berikutnya->isSameDay($log->tmt_jatuh_tempo)) {
            $log->update(['status' => 'dilewati', 'error' => 'Jadwal KGB pegawai sudah berubah.']);

            return;
        }

        $galat = [];

        // 1) WhatsApp (utama): satu provider sesuai pilihan di Pengaturan Integrasi
        $provider = Setting::waProvider();

        if ($provider === 'kirimdev' && $kirim->aktif()) {
            if (! $pegawai->no_hp) {
                $galat[] = 'WhatsApp: nomor HP pegawai kosong';
            } elseif (! $log->wa_template) {
                $galat[] = 'WhatsApp (KirimDev): template pesan belum diisi nama template WhatsApp-nya';
            } else {
                $hasil = $kirim->kirimTemplate(
                    $pegawai->no_hp,
                    $log->wa_template,
                    $log->wa_bahasa ?: 'id',
                    $log->wa_params ?? [],
                    $log->idempotency_key
                );

                if ($hasil['ok']) {
                    $this->tandaiWaTerkirim($log, $pegawai->no_hp, $hasil['respon']);

                    return;
                }

                $galat[] = 'WhatsApp (KirimDev): ' . $hasil['pesan'];
            }
        } elseif ($provider === 'fonnte' && $fonnte->aktif()) {
            if (! $pegawai->no_hp) {
                $galat[] = 'WhatsApp: nomor HP pegawai kosong';
            } else {
                // Fonnte mengirim teks bebas: pakai isi pesan template yang sudah terisi variabelnya
                $hasil = $fonnte->kirim($pegawai->no_hp, $log->pesan);

                if ($hasil['ok']) {
                    $this->tandaiWaTerkirim($log, $pegawai->no_hp, $hasil['respon']);

                    return;
                }

                $galat[] = 'WhatsApp (Fonnte): ' . $hasil['pesan'];
            }
        } else {
            $galat[] = 'WhatsApp: integrasi nonaktif atau belum lengkap';
        }

        // 2) Email (cadangan)
        if (Setting::bool('email_aktif')) {
            if ($pegawai->email) {
                try {
                    Mail::raw($log->pesan, function ($m) use ($pegawai, $log) {
                        $m->to($pegawai->email, $pegawai->nama)->subject($log->subjek ?: 'Pengingat KGB');
                    });

                    $log->update([
                        'status' => 'terkirim',
                        'kanal' => 'email',
                        'tujuan' => $pegawai->email,
                        'terkirim_at' => now(),
                    ]);

                    return;
                } catch (Throwable $e) {
                    $galat[] = 'Email: ' . Str::limit($e->getMessage(), 200);
                }
            } else {
                $galat[] = 'Email: alamat email pegawai kosong';
            }
        } else {
            $galat[] = 'Email: cadangan nonaktif';
        }

        $log->update(['status' => 'gagal', 'error' => implode(' | ', $galat)]);
    }

    private function tandaiWaTerkirim(RiwayatPengingat $log, string $noHp, ?string $respon): void
    {
        $log->update([
            'status' => 'terkirim',
            'kanal' => 'wa',
            'tujuan' => '+' . KirimDevService::normalisasiNomor($noHp),
            'respon' => $respon,
            'terkirim_at' => now(),
        ]);
    }

    public function failed(Throwable $e): void
    {
        RiwayatPengingat::whereKey($this->logId)
            ->where('status', 'diproses')
            ->update(['status' => 'gagal', 'error' => Str::limit($e->getMessage(), 500)]);
    }
}
