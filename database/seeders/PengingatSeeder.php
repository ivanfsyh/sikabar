<?php

namespace Database\Seeders;

use App\Models\AturanPengingat;
use App\Models\Setting;
use App\Models\TemplatePesan;
use Illuminate\Database\Seeder;

class PengingatSeeder extends Seeder
{
    public function run(): void
    {
        $template = TemplatePesan::firstOrCreate(
            ['nama' => 'Pengingat KGB Standar'],
            [
                'subjek' => 'Pengingat Kenaikan Gaji Berkala (KGB)',
                'isi' => "Yth. Bapak/Ibu {nama},\n\nKenaikan Gaji Berkala (KGB) Anda dijadwalkan pada {tmt_kgb_berikutnya} ({sisa_hari}).\nMohon segera menyiapkan berkas persyaratan KGB dan menyerahkannya kepada bagian kepegawaian.\n\nTerima kasih.\n{unit_kerja}",
                'wa_template' => 'pengingat_kgb',
                'wa_bahasa' => 'id',
                'wa_params' => [
                    ['nama' => null, 'nilai' => '{nama}'],
                    ['nama' => null, 'nilai' => '{tmt_kgb_berikutnya}'],
                    ['nama' => null, 'nilai' => '{sisa_hari}'],
                ],
            ]
        );

        foreach ([[60, 'Pengingat awal (H-60)'], [30, 'Pengingat H-30'], [7, 'Pengingat H-7'], [0, 'Pengingat hari H']] as [$hari, $nama]) {
            AturanPengingat::firstOrCreate(
                ['hari_sebelum' => $hari],
                ['nama' => $nama, 'template_pesan_id' => $template->id, 'aktif' => true]
            );
        }

        if (Setting::get('jam_kirim') === null) {
            Setting::set('jam_kirim', '08:00');
            Setting::set('toleransi_hari', '2');
        }
    }
}
