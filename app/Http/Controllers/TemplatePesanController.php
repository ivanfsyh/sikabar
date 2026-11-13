<?php

namespace App\Http\Controllers;

use App\Models\TemplatePesan;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TemplatePesanController extends Controller
{
    public function index()
    {
        $templates = TemplatePesan::withCount('aturan')->orderBy('nama')->get();

        return view('pengingat.template', compact('templates'));
    }

    public function store(Request $request)
    {
        TemplatePesan::create($this->validated($request));

        return back()->with('success', 'Template pesan berhasil ditambahkan.');
    }

    public function update(Request $request, TemplatePesan $template)
    {
        $template->update($this->validated($request));

        return back()->with('success', 'Template pesan berhasil diperbarui.');
    }

    public function destroy(TemplatePesan $template)
    {
        if ($template->aturan()->exists()) {
            return back()->with('error', 'Template masih dipakai oleh aturan pengingat, tidak bisa dihapus.');
        }

        $template->delete();

        return back()->with('success', 'Template pesan berhasil dihapus.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'subjek' => ['required', 'string', 'max:255'],
            'isi' => ['required', 'string', 'max:3000'],
            'wa_template' => ['nullable', 'string', 'max:100', 'regex:/^[a-z0-9_]+$/'],
            'wa_bahasa' => ['nullable', 'string', 'max:10'],
            'params' => ['nullable', 'array', 'max:10'],
            'params.*.nilai' => ['required', 'string', 'max:255'],
            'params.*.nama' => ['nullable', 'string', 'max:100', 'regex:/^[a-z][a-z0-9_]*$/'],
        ], [
            'nama.required' => 'Nama template wajib diisi.',
            'subjek.required' => 'Subjek (untuk email) wajib diisi.',
            'isi.required' => 'Isi pesan wajib diisi.',
            'wa_template.regex' => 'Nama template WhatsApp hanya boleh huruf kecil, angka, dan garis bawah (contoh: pengingat_kgb).',
            'params.*.nilai.required' => 'Nilai parameter WhatsApp wajib diisi (contoh: {nama}).',
            'params.*.nama.regex' => 'Nama parameter hanya boleh huruf kecil, angka, dan garis bawah, diawali huruf.',
        ]);

        $params = array_values($data['params'] ?? []);
        $bernama = collect($params)->filter(fn($p) => filled($p['nama'] ?? null))->count();

        if ($bernama > 0 && $bernama < count($params)) {
            throw ValidationException::withMessages([
                'params' => 'Nama parameter harus diisi di semua baris, atau dikosongkan di semua baris (tidak boleh dicampur).',
            ]);
        }

        if (! empty($params) && empty($data['wa_template'])) {
            throw ValidationException::withMessages([
                'wa_template' => 'Isi nama template WhatsApp jika parameter dipakai.',
            ]);
        }

        return [
            'nama' => $data['nama'],
            'subjek' => $data['subjek'],
            'isi' => $data['isi'],
            'wa_template' => $data['wa_template'] ?? null,
            'wa_bahasa' => $data['wa_bahasa'] ?: 'id',
            'wa_params' => $params ?: null,
        ];
    }
}
