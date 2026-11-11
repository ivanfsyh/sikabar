<?php

namespace App\Http\Controllers;

use App\Exports\PegawaiTemplateExport;
use App\Imports\PegawaiImport;
use App\Models\Pegawai;
use App\Rules\NipValid;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class PegawaiImportController extends Controller
{
    private const SESSION_KEY = 'pegawai_import_valid';

    public function index()
    {
        return view('pegawai.import');
    }

    public function template()
    {
        return Excel::download(new PegawaiTemplateExport, 'template-import-pegawai.xlsx');
    }

    /** Tahap 1: baca file, validasi tiap baris, tampilkan pratinjau. */
    public function preview(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:2048'],
        ], [
            'file.required' => 'Pilih file Excel terlebih dahulu.',
            'file.mimes' => 'File harus berformat .xlsx, .xls, atau .csv.',
            'file.max' => 'Ukuran file maksimal 2 MB.',
        ]);

        $reader = new PegawaiImport;
        Excel::import($reader, $request->file('file'));

        $existing = Pegawai::withTrashed()->pluck('nip')->flip();
        $seenInFile = [];
        $valid = [];
        $invalid = [];

        foreach ($reader->rows as $i => $row) {
            $line = $i + 2; // baris 1 = header
            $result = $this->validateRow($row->toArray(), $existing, $seenInFile);

            if ($result['errors']) {
                $invalid[] = ['line' => $line, 'data' => $result['data'], 'errors' => $result['errors']];
            } else {
                $valid[] = $result['data'];
                $seenInFile[$result['data']['nip']] = true;
            }
        }

        $request->session()->put(self::SESSION_KEY, $valid);

        return view('pegawai.import', compact('valid', 'invalid'));
    }

    /** Tahap 2: simpan baris yang valid dalam satu transaksi. */
    public function store(Request $request)
    {
        $valid = $request->session()->pull(self::SESSION_KEY, []);

        if (empty($valid)) {
            return redirect()->route('pegawai.import')->with('error', 'Tidak ada data valid untuk disimpan. Unggah ulang file Anda.');
        }

        DB::transaction(function () use ($valid) {
            foreach ($valid as $data) {
                Pegawai::create($data);
            }
        });

        return redirect()->route('pegawai.index')->with('success', count($valid) . ' data pegawai berhasil diimpor.');
    }

    private function validateRow(array $row, $existing, array $seenInFile): array
    {
        $errors = [];

        // NIP harus terbaca sebagai teks: 18 digit melebihi presisi angka Excel
        $rawNip = $row['nip'] ?? null;
        if (is_int($rawNip) || is_float($rawNip)) {
            $errors[] = 'NIP terbaca sebagai angka. Ubah format kolom NIP menjadi Teks.';
            $nip = (string) $rawNip;
        } else {
            $nip = NipValid::normalize($rawNip);
        }

        if (! $errors) {
            if ($msg = NipValid::check($nip)) {
                $errors[] = $msg;
            } elseif (isset($existing[$nip])) {
                $errors[] = 'NIP sudah terdaftar di database.';
            } elseif (isset($seenInFile[$nip])) {
                $errors[] = 'NIP duplikat di dalam file.';
            }
        }

        $nama = trim((string) ($row['nama'] ?? ''));
        if ($nama === '') {
            $errors[] = 'Nama wajib diisi.';
        }

        $tglLahir = $this->parseDate($row['tanggal_lahir'] ?? null);
        if (! $tglLahir) {
            $errors[] = 'Tanggal lahir tidak valid (gunakan YYYY-MM-DD).';
        }

        $jk = strtoupper(trim((string) ($row['jenis_kelamin'] ?? '')));
        $jk = match ($jk) {
            'L', 'LAKI-LAKI', 'LAKI LAKI' => 'L',
            'P', 'PEREMPUAN' => 'P',
            default => null,
        };
        if (! $jk) {
            $errors[] = 'Jenis kelamin harus L atau P.';
        }

        // Cocokkan dengan data yang tertanam di NIP
        if (! $errors) {
            $parsed = NipValid::parse($nip);
            if ($tglLahir !== $parsed['tanggal_lahir']) {
                $errors[] = "Tanggal lahir tidak sesuai NIP ({$parsed['tanggal_lahir']}).";
            }
            if ($jk !== $parsed['jenis_kelamin']) {
                $errors[] = 'Jenis kelamin tidak sesuai digit ke-15 NIP.';
            }
        }

        $status = strtolower(trim((string) ($row['status'] ?? 'aktif'))) ?: 'aktif';
        if (! in_array($status, ['aktif', 'pensiun', 'pindah'], true)) {
            $errors[] = 'Status harus aktif, pensiun, atau pindah.';
        }

        $email = trim((string) ($row['email'] ?? ''));
        if ($email !== '' && ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Format email tidak valid.';
        }

        return [
            'errors' => $errors,
            'data' => [
                'nip' => $nip,
                'nama' => $nama,
                'tempat_lahir' => trim((string) ($row['tempat_lahir'] ?? '')) ?: null,
                'tanggal_lahir' => $tglLahir,
                'jenis_kelamin' => $jk,
                'pangkat_golongan' => trim((string) ($row['pangkat_golongan'] ?? '')) ?: null,
                'jabatan' => trim((string) ($row['jabatan'] ?? '')) ?: null,
                'unit_kerja' => trim((string) ($row['unit_kerja'] ?? '')) ?: null,
                'tmt_cpns' => $this->parseDate($row['tmt_cpns'] ?? null),
                'status' => $status,
                'no_hp' => trim((string) ($row['no_hp'] ?? '')) ?: null,
                'email' => $email ?: null,
            ],
        ];
    }

    /** Terima teks YYYY-MM-DD / DD-MM-YYYY / DD/MM/YYYY, atau serial tanggal Excel. */
    private function parseDate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            if (is_numeric($value)) {
                return Carbon::instance(ExcelDate::excelToDateTimeObject($value))->format('Y-m-d');
            }

            $value = trim((string) $value);
            foreach (['Y-m-d', 'd-m-Y', 'd/m/Y'] as $format) {
                $date = Carbon::createFromFormat($format, $value);
                if ($date && $date->format($format) === $value) {
                    return $date->format('Y-m-d');
                }
            }
        } catch (\Throwable $e) {
            return null;
        }

        return null;
    }
}
