<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AturanPengingat extends Model
{
    protected $fillable = ['nama', 'hari_sebelum', 'template_pesan_id', 'aktif'];

    protected function casts(): array
    {
        return ['aktif' => 'boolean', 'hari_sebelum' => 'integer'];
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(TemplatePesan::class, 'template_pesan_id');
    }

    public function getLabelAttribute(): string
    {
        return match (true) {
            $this->hari_sebelum > 0 => 'H-' . $this->hari_sebelum,
            $this->hari_sebelum === 0 => 'Hari H',
            default => 'H+' . abs($this->hari_sebelum),
        };
    }
}
