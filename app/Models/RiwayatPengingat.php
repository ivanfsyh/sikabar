<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiwayatPengingat extends Model
{
    protected $fillable = [
        'pegawai_id',
        'aturan_pengingat_id',
        'nama_aturan',
        'tmt_jatuh_tempo',
        'kanal',
        'status',
        'subjek',
        'pesan',
        'wa_template',
        'wa_bahasa',
        'wa_params',
        'idempotency_key',
        'tujuan',
        'percobaan',
        'respon',
        'error',
        'terkirim_at',
    ];

    protected function casts(): array
    {
        return [
            'tmt_jatuh_tempo' => 'date',
            'terkirim_at' => 'datetime',
            'wa_params' => 'array',
        ];
    }

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class)->withTrashed();
    }

    public function aturan(): BelongsTo
    {
        return $this->belongsTo(AturanPengingat::class, 'aturan_pengingat_id');
    }
}
