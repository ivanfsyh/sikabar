<?php

namespace App\Http\Controllers;

use App\Exports\LaporanKgbExport;
use App\Services\LaporanKgbService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class LaporanKgbController extends Controller
{
    public function index(Request $request, LaporanKgbService $service)
    {
        $filter = $this->filter($request);
        $laporan = $service->susun($filter);

        $perHalaman = 15;
        $halaman = LengthAwarePaginator::resolveCurrentPage();

        $baris = new LengthAwarePaginator(
            $laporan['rows']->forPage($halaman, $perHalaman)->values(),
            $laporan['rows']->count(),
            $perHalaman,
            $halaman,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('pimpinan.laporan', [
            'baris' => $baris,
            'ringkasan' => $laporan['ringkasan'],
            'perUnit' => $laporan['perUnit'],
            'pengingat' => $laporan['pengingat'],
            'unitTersedia' => $service->unitTersedia(),
            'statusList' => LaporanKgbService::STATUS,
            'filter' => $filter,
        ]);
    }

    public function cetak(Request $request, LaporanKgbService $service)
    {
        $filter = $this->filter($request);
        $laporan = $service->susun($filter);

        return view('pimpinan.laporan-cetak', $laporan + [
            'statusList' => LaporanKgbService::STATUS,
            'filter' => $filter,
        ]);
    }

    public function ekspor(Request $request, LaporanKgbService $service)
    {
        $laporan = $service->susun($this->filter($request));

        return Excel::download(
            new LaporanKgbExport($laporan['rows']),
            'laporan-kgb-' . now()->format('Ymd') . '.xlsx'
        );
    }

    private function filter(Request $request): array
    {
        return $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'unit' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in(array_keys(LaporanKgbService::STATUS))],
            'dari' => ['nullable', 'date'],
            'sampai' => ['nullable', 'date', 'after_or_equal:dari'],
        ]);
    }
}
