<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan KGB - SIKABAR</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #222;
            margin: 24px;
        }

        h1 {
            font-size: 16px;
            margin: 0;
        }

        .sub {
            color: #666;
            margin: 2px 0 14px;
        }

        .ringkas {
            display: flex;
            gap: 10px;
            margin-bottom: 14px;
        }

        .ringkas div {
            border: 1px solid #ccc;
            padding: 6px 12px;
            border-radius: 4px;
        }

        .ringkas b {
            display: block;
            font-size: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
        }

        th,
        td {
            border: 1px solid #999;
            padding: 4px 6px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background: #eee;
        }

        h2 {
            font-size: 13px;
            margin: 16px 0 6px;
        }

        .r {
            text-align: right;
        }

        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body onload="window.print()">

    @php
        $rupiah = fn($n) => $n === null ? '-' : 'Rp ' . number_format($n, 0, ',', '.');
    @endphp

    <button class="no-print" onclick="window.print()">Cetak</button>

    <h1>Laporan Kenaikan Gaji Berkala (KGB)</h1>
    <p class="sub">
        Puskesmas Sunyaragi &mdash; dicetak {{ now()->translatedFormat('d F Y H:i') }}
        @if (!empty($filter['unit']))
            &mdash; Unit: {{ $filter['unit'] }}
        @endif
        @if (!empty($filter['status']))
            &mdash; Status: {{ $statusList[$filter['status']] }}
        @endif
        @if (!empty($filter['dari']) || !empty($filter['sampai']))
            &mdash; Periode: {{ $filter['dari'] ?? '...' }} s/d {{ $filter['sampai'] ?? '...' }}
        @endif
    </p>

    <div class="ringkas">
        <div>Total<b>{{ $ringkasan['total'] }}</b></div>
        <div>Terlewat<b>{{ $ringkasan['terlewat'] }}</b></div>
        <div>Bulan ini<b>{{ $ringkasan['bulan_ini'] }}</b></div>
        <div>Mendatang<b>{{ $ringkasan['mendatang'] }}</b></div>
        <div>Belum ada data<b>{{ $ringkasan['belum_ada'] }}</b></div>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>NIP</th>
                <th>Nama</th>
                <th>Unit / Jabatan</th>
                <th>Gol.</th>
                <th>KGB Terakhir</th>
                <th>KGB Berikutnya</th>
                <th>Status</th>
                <th class="r">Gaji Pokok</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $r)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $r['pegawai']->nip }}</td>
                    <td>{{ $r['pegawai']->nama }}</td>
                    <td>{{ $r['pegawai']->unit_kerja ?: '-' }} / {{ $r['pegawai']->jabatan ?: '-' }}</td>
                    <td>{{ $r['pegawai']->pangkat_golongan ?: '-' }}</td>
                    <td>{{ $r['tmt_terakhir']?->translatedFormat('d M Y') ?? '-' }}</td>
                    <td>{{ $r['tanggal']?->translatedFormat('d M Y') ?? '-' }}</td>
                    <td>{{ $statusList[$r['status']] }}</td>
                    <td class="r">{{ $rupiah($r['gaji_sekarang']) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" style="text-align:center">Tidak ada data.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <h2>Ringkasan per Unit Kerja</h2>
    <table>
        <thead>
            <tr>
                <th>Unit</th>
                <th>Total</th>
                <th>Terlewat</th>
                <th>Bulan Ini</th>
                <th>Mendatang</th>
                <th>Belum Ada</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($perUnit as $u)
                <tr>
                    <td>{{ $u['unit'] }}</td>
                    <td>{{ $u['total'] }}</td>
                    <td>{{ $u['terlewat'] }}</td>
                    <td>{{ $u['bulan_ini'] }}</td>
                    <td>{{ $u['mendatang'] }}</td>
                    <td>{{ $u['belum_ada'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

</body>

</html>
