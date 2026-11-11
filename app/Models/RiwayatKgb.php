<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiwayatKgb extends Model
{
    protected $fillable = [
        'pegawai_id',
        'no_sk',
        'tanggal_sk',
        'pejabat_penandatangan',
        'tmt_kgb',
        'golongan_ruang',
        'masa_kerja_tahun',
        'masa_kerja_bulan',
        'gaji_pokok_lama',
        'gaji_pokok_baru',
        'tmt_kgb_berikutnya',
        'file_sk',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_sk' => 'date',
            'tmt_kgb' => 'date',
            'tmt_kgb_berikutnya' => 'date',
        ];
    }

    protected static function booted(): void
    {
        // KGB berikutnya otomatis = TMT KGB + 2 tahun
        static::saving(function (RiwayatKgb $kgb) {
            $kgb->tmt_kgb_berikutnya = $kgb->tmt_kgb->copy()->addYears(2);
        });
    }

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class);
    }
}
