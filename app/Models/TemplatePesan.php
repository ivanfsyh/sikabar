<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TemplatePesan extends Model
{
    protected $fillable = ['nama', 'subjek', 'isi', 'wa_template', 'wa_bahasa', 'wa_params'];

    protected function casts(): array
    {
        return ['wa_params' => 'array'];
    }

    public function aturan(): HasMany
    {
        return $this->hasMany(AturanPengingat::class);
    }
}
