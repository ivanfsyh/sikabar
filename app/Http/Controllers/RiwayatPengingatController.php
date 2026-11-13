<?php

namespace App\Http\Controllers;

use App\Jobs\KirimPengingatJob;
use App\Models\RiwayatPengingat;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RiwayatPengingatController extends Controller
{
    public function index(Request $request)
    {
        $riwayat = RiwayatPengingat::with('pegawai')
            ->when($request->status, fn($q, $v) => $q->where('status', $v))
            ->when($request->q, fn($q, $v) => $q->whereHas('pegawai', fn($p) => $p->search($v)))
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $ringkasan = RiwayatPengingat::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        return view('pengingat.riwayat', compact('riwayat', 'ringkasan'));
    }

    public function kirimUlang(RiwayatPengingat $riwayat)
    {
        // Transisi atomik: hanya yang berstatus gagal, mencegah klik ganda.
        // Kunci idempotensi diganti agar KirimDev tidak mengembalikan hasil gagal lama dari cache.
        $diubah = RiwayatPengingat::whereKey($riwayat->id)
            ->where('status', 'gagal')
            ->update(['status' => 'menunggu', 'error' => null, 'idempotency_key' => (string) Str::uuid()]);

        if ($diubah) {
            KirimPengingatJob::dispatch($riwayat->id);
        }

        return back()->with(
            $diubah ? 'success' : 'error',
            $diubah ? 'Pengiriman ulang dijadwalkan.' : 'Hanya pengingat berstatus gagal yang bisa dikirim ulang.'
        );
    }
}
