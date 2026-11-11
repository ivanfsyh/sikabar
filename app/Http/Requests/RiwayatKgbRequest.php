<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RiwayatKgbRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'no_sk' => ['required', 'string', 'max:100'],
            'tanggal_sk' => ['required', 'date'],
            'pejabat_penandatangan' => ['nullable', 'string', 'max:255'],
            'tmt_kgb' => ['required', 'date'],
            'golongan_ruang' => ['nullable', 'string', 'max:100'],
            'masa_kerja_tahun' => ['required', 'integer', 'min:0', 'max:50'],
            'masa_kerja_bulan' => ['required', 'integer', 'min:0', 'max:11'],
            'gaji_pokok_lama' => ['required', 'integer', 'min:0'],
            'gaji_pokok_baru' => ['required', 'integer', 'gt:gaji_pokok_lama'],
            'file_sk' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
            'keterangan' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'no_sk.required' => 'Nomor SK wajib diisi.',
            'tanggal_sk.required' => 'Tanggal SK wajib diisi.',
            'tmt_kgb.required' => 'TMT KGB wajib diisi.',
            'gaji_pokok_baru.required' => 'Gaji pokok baru wajib diisi.',
            'gaji_pokok_baru.gt' => 'Gaji pokok baru harus lebih besar dari gaji pokok lama.',
            'file_sk.mimes' => 'File SK harus berformat PDF, JPG, atau PNG.',
            'file_sk.max' => 'Ukuran file SK maksimal 2 MB.',
        ];
    }
}
