<?php

namespace App\Http\Controllers;

use App\Http\Requests\RiwayatKgbRequest;
use App\Models\Pegawai;
use App\Models\RiwayatKgb;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class RiwayatKgbController extends Controller
{
    /** Daftar seluruh riwayat KGB (menu "Riwayat KGB"). */
    public function index()
    {
        $riwayat = RiwayatKgb::with('pegawai')
            ->when(request('q'), function ($q, $keyword) {
                $q->whereHas('pegawai', fn($p) => $p->search($keyword));
            })
            ->orderByDesc('tmt_kgb')
            ->paginate(10)
            ->withQueryString();

        return view('kgb.index', compact('riwayat'));
    }

    public function store(RiwayatKgbRequest $request, Pegawai $pegawai)
    {
        $data = $request->validated();
        $this->assertTmtBaru($pegawai, $data['tmt_kgb']);

        if ($request->hasFile('file_sk')) {
            $data['file_sk'] = $request->file('file_sk')->store('sk-kgb', 'public');
        }

        $pegawai->riwayatKgb()->create($data);

        return redirect()->route('pegawai.show', $pegawai)->with('success', 'Riwayat KGB berhasil ditambahkan.');
    }

    public function update(RiwayatKgbRequest $request, Pegawai $pegawai, RiwayatKgb $kgb)
    {
        abort_unless($kgb->pegawai_id === $pegawai->id, 404);

        $data = $request->validated();

        if ($request->hasFile('file_sk')) {
            if ($kgb->file_sk) {
                Storage::disk('public')->delete($kgb->file_sk);
            }
            $data['file_sk'] = $request->file('file_sk')->store('sk-kgb', 'public');
        }

        $kgb->update($data);

        return redirect()->route('pegawai.show', $pegawai)->with('success', 'Riwayat KGB berhasil diperbarui.');
    }

    public function destroy(Pegawai $pegawai, RiwayatKgb $kgb)
    {
        abort_unless($kgb->pegawai_id === $pegawai->id, 404);

        if ($kgb->file_sk) {
            Storage::disk('public')->delete($kgb->file_sk);
        }
        $kgb->delete();

        return redirect()->route('pegawai.show', $pegawai)->with('success', 'Riwayat KGB berhasil dihapus.');
    }

    /** Saat menambah, TMT KGB harus lebih baru dari TMT KGB terakhir pegawai. */
    private function assertTmtBaru(Pegawai $pegawai, string $tmt): void
    {
        $terakhir = RiwayatKgb::where('pegawai_id', $pegawai->id)->max('tmt_kgb');

        if ($terakhir && $tmt <= substr($terakhir, 0, 10)) {
            throw ValidationException::withMessages([
                'tmt_kgb' => 'TMT KGB harus lebih baru dari TMT KGB sebelumnya (' . substr($terakhir, 0, 10) . ').',
            ]);
        }
    }
}
