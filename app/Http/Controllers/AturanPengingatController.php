<?php

namespace App\Http\Controllers;

use App\Models\AturanPengingat;
use App\Models\TemplatePesan;
use Illuminate\Http\Request;

class AturanPengingatController extends Controller
{
    public function index()
    {
        $aturan = AturanPengingat::with('template')->orderByDesc('hari_sebelum')->get();
        $templates = TemplatePesan::orderBy('nama')->get();

        return view('pengingat.aturan', compact('aturan', 'templates'));
    }

    public function store(Request $request)
    {
        AturanPengingat::create($this->validated($request));

        return back()->with('success', 'Aturan pengingat berhasil ditambahkan.');
    }

    public function update(Request $request, AturanPengingat $aturan)
    {
        $aturan->update($this->validated($request));

        return back()->with('success', 'Aturan pengingat berhasil diperbarui.');
    }

    public function toggle(AturanPengingat $aturan)
    {
        $aturan->update(['aktif' => ! $aturan->aktif]);

        return back()->with('success', 'Aturan ' . ($aturan->aktif ? 'diaktifkan.' : 'dinonaktifkan.'));
    }

    public function destroy(AturanPengingat $aturan)
    {
        $aturan->delete(); // riwayat pengiriman tetap tersimpan

        return back()->with('success', 'Aturan pengingat berhasil dihapus.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'hari_sebelum' => ['required', 'integer', 'between:-365,365'],
            'template_pesan_id' => ['required', 'exists:template_pesans,id'],
        ], [
            'nama.required' => 'Nama aturan wajib diisi.',
            'hari_sebelum.required' => 'Rentang waktu wajib diisi.',
            'hari_sebelum.between' => 'Rentang waktu harus antara -365 dan 365 hari.',
            'template_pesan_id.required' => 'Pilih template pesan.',
        ]);

        $data['aktif'] = $request->boolean('aktif');

        return $data;
    }
}
