<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;

class DashboardController extends Controller
{
    public function admin()
    {
        return view('admin.dashboard', $this->data());
    }

    public function pimpinan()
    {
        return view('pimpinan.dashboard', $this->data());
    }

    private function data(): array
    {
        $awalBulan = now()->startOfMonth();
        $akhirBulan = now()->endOfMonth();
        $batasMendatang = now()->addMonths(3)->endOfMonth();

        $aktif = Pegawai::aktif()->with('kgbTerbaru')->get();

        // Jadwal KGB berikutnya per pegawai aktif (berdasarkan KGB terbaru)
        $jadwal = $aktif
            ->filter(fn($p) => $p->kgbTerbaru)
            ->map(fn($p) => [
                'pegawai' => $p,
                'tanggal' => $p->kgbTerbaru->tmt_kgb_berikutnya,
            ]);

        $terlewat = $jadwal->filter(fn($j) => $j['tanggal']->lt($awalBulan));
        $bulanIni = $jadwal->filter(fn($j) => $j['tanggal']->between($awalBulan, $akhirBulan));
        $mendatang = $jadwal->filter(fn($j) => $j['tanggal']->gt($akhirBulan) && $j['tanggal']->lte($batasMendatang));

        // Tabel: yang terlewat, bulan ini, dan 3 bulan ke depan (urut tanggal terdekat)
        $daftarJadwal = $jadwal
            ->filter(fn($j) => $j['tanggal']->lte($batasMendatang))
            ->sortBy(fn($j) => $j['tanggal']->timestamp)
            ->take(10)
            ->values();

        return [
            'totalPegawai' => Pegawai::count(),
            'pegawaiAktif' => $aktif->count(),
            'kgbBulanIni' => $bulanIni->count(),
            'kgbMendatang' => $mendatang->count(),
            'kgbTerlewat' => $terlewat->count(),
            'belumAdaKgb' => $aktif->filter(fn($p) => ! $p->kgbTerbaru)->count(),
            'daftarJadwal' => $daftarJadwal,
            'awalBulan' => $awalBulan,
        ];
    }
}
