<?php

namespace App\Services;

use App\Models\Pegawai;
use App\Models\RiwayatPengingat;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class LaporanKgbService
{
    public const STATUS = [
        'terlewat'  => 'Terlewat',
        'bulan_ini' => 'Bulan ini',
        'mendatang' => 'Mendatang',
        'belum_ada' => 'Belum ada data KGB',
    ];

    /** Susun seluruh data laporan berdasarkan filter. */
    public function susun(array $filter): array
    {
        $hariIni = today();
        $awalBulan = now()->startOfMonth();
        $akhirBulan = now()->endOfMonth();

        $dari = ! empty($filter['dari']) ? Carbon::parse($filter['dari'])->startOfDay() : null;
        $sampai = ! empty($filter['sampai']) ? Carbon::parse($filter['sampai'])->endOfDay() : null;

        $rows = Pegawai::aktif()
            ->with('kgbTerbaru')
            ->search($filter['q'] ?? null)
            ->when($filter['unit'] ?? null, fn($q, $unit) => $q->where('unit_kerja', $unit))
            ->orderBy('nama')
            ->get()
            ->map(function (Pegawai $p) use ($hariIni, $awalBulan, $akhirBulan) {
                $kgb = $p->kgbTerbaru;
                $tanggal = $kgb?->tmt_kgb_berikutnya?->copy()->startOfDay();

                if (! $kgb) {
                    $status = 'belum_ada';
                } elseif ($tanggal->lt($awalBulan)) {
                    $status = 'terlewat';
                } elseif ($tanggal->lte($akhirBulan)) {
                    $status = 'bulan_ini';
                } else {
                    $status = 'mendatang';
                }

                return [
                    'pegawai' => $p,
                    'tmt_terakhir' => $kgb?->tmt_kgb,
                    'tanggal' => $tanggal,
                    'status' => $status,
                    'sisa_hari' => $tanggal ? (int) $hariIni->diffInDays($tanggal, false) : null,
                    'gaji_sekarang' => $kgb?->gaji_pokok_baru,
                    // Kenaikan pada KGB terakhir (null jika gaji lama tidak tercatat)
                    'kenaikan_terakhir' => ($kgb && $kgb->gaji_pokok_lama > 0)
                        ? max(0, $kgb->gaji_pokok_baru - $kgb->gaji_pokok_lama)
                        : null,
                ];
            })
            ->when($filter['status'] ?? null, fn(Collection $c, $s) => $c->where('status', $s))
            ->when($dari, fn(Collection $c) => $c->filter(fn($r) => $r['tanggal'] && $r['tanggal']->gte($dari)))
            ->when($sampai, fn(Collection $c) => $c->filter(fn($r) => $r['tanggal'] && $r['tanggal']->lte($sampai)))
            ->sortBy(fn($r) => $r['tanggal']?->timestamp ?? PHP_INT_MAX)
            ->values();

        $ringkasan = [
            'total' => $rows->count(),
            'terlewat' => $rows->where('status', 'terlewat')->count(),
            'bulan_ini' => $rows->where('status', 'bulan_ini')->count(),
            'mendatang' => $rows->where('status', 'mendatang')->count(),
            'belum_ada' => $rows->where('status', 'belum_ada')->count(),
            // Perkiraan kasar: dianggap naik sebesar kenaikan KGB terakhir masing-masing pegawai
            'estimasi_kenaikan' => (int) $rows->whereIn('status', ['terlewat', 'bulan_ini', 'mendatang'])->sum('kenaikan_terakhir'),
        ];

        $perUnit = $rows
            ->groupBy(fn($r) => $r['pegawai']->unit_kerja ?: 'Tanpa unit kerja')
            ->map(fn($g, $unit) => [
                'unit' => $unit,
                'total' => $g->count(),
                'terlewat' => $g->where('status', 'terlewat')->count(),
                'bulan_ini' => $g->where('status', 'bulan_ini')->count(),
                'mendatang' => $g->where('status', 'mendatang')->count(),
                'belum_ada' => $g->where('status', 'belum_ada')->count(),
            ])
            ->sortByDesc('terlewat')
            ->values();

        $pengingat = array_merge(
            array_fill_keys(['menunggu', 'diproses', 'terkirim', 'gagal', 'dilewati'], 0),
            RiwayatPengingat::where('created_at', '>=', now()->subDays(30))
                ->selectRaw('status, count(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status')
                ->all()
        );

        return compact('rows', 'ringkasan', 'perUnit', 'pengingat');
    }

    /** Daftar unit kerja untuk dropdown filter. */
    public function unitTersedia(): Collection
    {
        return Pegawai::aktif()
            ->whereNotNull('unit_kerja')
            ->where('unit_kerja', '!=', '')
            ->distinct()
            ->orderBy('unit_kerja')
            ->pluck('unit_kerja');
    }
}
