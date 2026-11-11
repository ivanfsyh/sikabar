<?php

namespace App\Http\Controllers;

use App\Http\Requests\PegawaiRequest;
use App\Models\Pegawai;
use Illuminate\Http\Request;

class PegawaiController extends Controller
{
    public function index(Request $request)
    {
        $pegawais = Pegawai::query()
            ->with('kgbTerbaru')
            ->search($request->q)
            ->when($request->status, fn($q, $v) => $q->where('status', $v))
            ->when($request->unit_kerja, fn($q, $v) => $q->where('unit_kerja', $v))
            ->orderBy('nama')
            ->paginate(10)
            ->withQueryString();

        $unitKerja = Pegawai::whereNotNull('unit_kerja')->distinct()->orderBy('unit_kerja')->pluck('unit_kerja');

        return view('pegawai.index', compact('pegawais', 'unitKerja'));
    }

    public function create()
    {
        return view('pegawai.create', ['pegawai' => new Pegawai(['status' => 'aktif'])]);
    }

    public function store(PegawaiRequest $request)
    {
        Pegawai::create($request->validated());

        return redirect()->route('pegawai.index')->with('success', 'Data pegawai berhasil ditambahkan.');
    }

    public function show(Pegawai $pegawai)
    {
        $pegawai->load('riwayatKgb');

        return view('pegawai.show', compact('pegawai'));
    }

    public function edit(Pegawai $pegawai)
    {
        return view('pegawai.edit', compact('pegawai'));
    }

    public function update(PegawaiRequest $request, Pegawai $pegawai)
    {
        $pegawai->update($request->validated());

        return redirect()->route('pegawai.show', $pegawai)->with('success', 'Data pegawai berhasil diperbarui.');
    }

    public function destroy(Pegawai $pegawai)
    {
        $pegawai->delete(); // soft delete, riwayat KGB tetap tersimpan

        return redirect()->route('pegawai.index')->with('success', 'Data pegawai berhasil dihapus.');
    }
}
