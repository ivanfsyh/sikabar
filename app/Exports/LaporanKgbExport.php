<?php

namespace App\Exports;

use App\Services\LaporanKgbService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;

class LaporanKgbExport extends DefaultValueBinder implements FromArray, WithHeadings, ShouldAutoSize, WithCustomValueBinder
{
    public function __construct(private Collection $rows) {}

    public function headings(): array
    {
        return [
            'No',
            'NIP',
            'Nama',
            'Jabatan',
            'Unit Kerja',
            'Pangkat/Golongan',
            'TMT KGB Terakhir',
            'KGB Berikutnya',
            'Sisa Hari',
            'Status',
            'Gaji Pokok Saat Ini',
            'Kenaikan KGB Terakhir',
        ];
    }

    public function array(): array
    {
        return $this->rows->values()->map(fn($r, $i) => [
            $i + 1,
            $r['pegawai']->nip,
            $r['pegawai']->nama,
            $r['pegawai']->jabatan,
            $r['pegawai']->unit_kerja,
            $r['pegawai']->pangkat_golongan,
            $r['tmt_terakhir']?->format('d-m-Y'),
            $r['tanggal']?->format('d-m-Y'),
            $r['sisa_hari'],
            LaporanKgbService::STATUS[$r['status']],
            $r['gaji_sekarang'],
            $r['kenaikan_terakhir'],
        ])->all();
    }

    /** Kolom B (NIP, 18 digit) wajib disimpan sebagai teks agar tidak rusak. */
    public function bindValue(Cell $cell, mixed $value): bool
    {
        if ($cell->getColumn() === 'B') {
            $cell->setValueExplicit((string) $value, DataType::TYPE_STRING);

            return true;
        }

        return parent::bindValue($cell, $value);
    }
}
