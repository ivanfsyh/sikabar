<?php

namespace App\Http\Requests;

use App\Rules\NipValid;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PegawaiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['nip' => NipValid::normalize($this->nip)]);
    }

    public function rules(): array
    {
        $id = $this->route('pegawai')?->id;

        return [
            'nip' => ['required', new NipValid, Rule::unique('pegawais', 'nip')->ignore($id)],
            'nama' => ['required', 'string', 'max:255'],
            'tempat_lahir' => ['nullable', 'string', 'max:100'],
            'tanggal_lahir' => ['required', 'date', 'before:today'],
            'jenis_kelamin' => ['required', Rule::in(['L', 'P'])],
            'pangkat_golongan' => ['nullable', 'string', 'max:100'],
            'jabatan' => ['nullable', 'string', 'max:255'],
            'unit_kerja' => ['nullable', 'string', 'max:255'],
            'tmt_cpns' => ['nullable', 'date'],
            'status' => ['required', Rule::in(['aktif', 'pensiun', 'pindah'])],
            'no_hp' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'nip.required' => 'NIP wajib diisi.',
            'nip.unique' => 'NIP sudah terdaftar.',
            'nama.required' => 'Nama wajib diisi.',
            'tanggal_lahir.required' => 'Tanggal lahir wajib diisi.',
            'tanggal_lahir.before' => 'Tanggal lahir harus sebelum hari ini.',
            'jenis_kelamin.required' => 'Jenis kelamin wajib dipilih.',
            'email.email' => 'Format email tidak valid.',
        ];
    }

    /** Cocokkan tanggal lahir & jenis kelamin di form dengan data di dalam NIP. */
    public function after(): array
    {
        return [
            function ($validator) {
                if ($validator->errors()->has('nip')) {
                    return;
                }
                $parsed = NipValid::parse($this->nip);
                if ($this->tanggal_lahir && $this->tanggal_lahir !== $parsed['tanggal_lahir']) {
                    $validator->errors()->add('tanggal_lahir', 'Tanggal lahir tidak sesuai dengan 8 digit pertama NIP (' . $parsed['tanggal_lahir'] . ').');
                }
                if ($this->jenis_kelamin && $this->jenis_kelamin !== $parsed['jenis_kelamin']) {
                    $validator->errors()->add('jenis_kelamin', 'Jenis kelamin tidak sesuai dengan digit ke-15 NIP.');
                }
            },
        ];
    }
}
