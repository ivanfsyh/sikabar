<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class PegawaiTemplateExport implements FromArray, WithHeadings, ShouldAutoSize, WithColumnFormatting
{
    public function headings(): array
    {
        return [
            'nip',
            'nama',
            'tempat_lahir',
            'tanggal_lahir',
            'jenis_kelamin',
            'pangkat_golongan',
            'jabatan',
            'unit_kerja',
            'tmt_cpns',
            'status',
            'no_hp',
            'email',
        ];
    }

    public function array(): array
    {
        return [[
            '198501152010011001',
            'Contoh Nama, S.Kep',
            'Cirebon',
            '1985-01-15',
            'L',
            'III/b',
            'Perawat Ahli Muda',
            'Puskesmas Sunyaragi',
            '2010-01-01',
            'aktif',
            '081234567890',
            'contoh@email.com',
        ]];
    }

    public function columnFormats(): array
    {
        // Kolom A (NIP), D (tgl lahir), I (TMT CPNS), K (no HP) sebagai teks
        return [
            'A' => NumberFormat::FORMAT_TEXT,
            'D' => NumberFormat::FORMAT_TEXT,
            'I' => NumberFormat::FORMAT_TEXT,
            'K' => NumberFormat::FORMAT_TEXT,
        ];
    }
}
