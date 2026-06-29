<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Hasil Ranking Prioritas Penagihan</title>
    <style>
        @page { margin: 40px 50px; }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 10pt;
            color: #333;
            margin: 0;
            padding: 0;
        }

        /* HEADER */
        .header { width: 100%; border-bottom: 2px solid #005baa; padding-bottom: 10px; margin-bottom: 20px; }
        .header table { width: 100%; border-collapse: collapse; }
        .header td { vertical-align: middle; }
        .logo { width: 60px; }
        .title-area { text-align: center; }
        .title { font-size: 16pt; font-weight: bold; color: #005baa; margin: 0 0 5px 0; text-transform: uppercase; }
        .subtitle { font-size: 10pt; color: #555; margin: 0; }

        .meta-info { margin-bottom: 20px; font-size: 9pt; }
        .meta-info table { width: 100%; }
        .meta-info td { vertical-align: top; padding: 2px 0; }
        .meta-label { font-weight: bold; width: 120px; color: #555; }

        /* TABLE */
        .table-wrap { width: 100%; margin-bottom: 20px; }
        table.data-table { width: 100%; border-collapse: collapse; font-size: 9pt; }
        table.data-table th, table.data-table td { border: 1px solid #ddd; padding: 8px; }
        table.data-table th { background-color: #f4f6f9; color: #005baa; font-weight: bold; text-align: left; text-transform: uppercase; }
        table.data-table th.center, table.data-table td.center { text-align: center; }
        table.data-table th.right,  table.data-table td.right  { text-align: right; }

        /* BADGES */
        .badge { display: inline-block; padding: 3px 8px; border-radius: 10px; font-size: 8pt; font-weight: bold; color: #fff; text-align: center; }
        .bg-sangat-prioritas { background-color: #DC2626; }
        .bg-prioritas        { background-color: #EA580C; }
        .bg-sedang           { background-color: #D97706; }
        .bg-rendah           { background-color: #16A34A; }

        /* FOOTER */
        .footer { position: fixed; bottom: -20px; left: 0; right: 0; width: 100%; border-top: 1px solid #ddd; padding-top: 10px; font-size: 8pt; color: #777; }
        .footer table { width: 100%; }
        .footer td { vertical-align: top; }
        .page-number:after { content: counter(page); }
    </style>
</head>
<body>

    {{-- Bagian Header Laporan PDF --}}
    <div class="header">
        <table>
            <tr>
                <td style="width: 80px;">
                    @if(file_exists(public_path('images/logo_pln.png')))
                        <img src="{{ public_path('images/logo_pln.png') }}" class="logo" alt="Logo PLN">
                    @else
                        <h2 style="color:#005baa; margin:0;">PLN</h2>
                    @endif
                </td>
                <td class="title-area">
                    <h1 class="title">Laporan Hasil Ranking Prioritas Penagihan</h1>
                    <p class="subtitle">Sistem Pendukung Keputusan Penagihan (Metode SAW)</p>
                </td>
                <td style="width: 80px;"></td>
            </tr>
        </table>
    </div>

    {{-- Informasi meta laporan seperti tanggal cetak dan filter aktif --}}
    <div class="meta-info">
        <table>
            <tr>
                <td class="meta-label">Tanggal Export</td>
                <td>: {{ \Carbon\Carbon::now()->format('d F Y H:i') }}</td>
                <td class="meta-label">Total Data</td>
                <td>: {{ count($ranking) }} Pelanggan</td>
            </tr>
            <tr>
                <td class="meta-label">User Export</td>
                <td>: {{ $user->name ?? 'System' }}</td>
                <td class="meta-label">Filter Aktif</td>
                <td>:
                    @php
                        $filterTexts = [];
                        if (!empty($filters['search']))       $filterTexts[] = 'Pencarian: '  . $filters['search'];
                        if (!empty($filters['kategori']))     $filterTexts[] = 'Kategori: '   . $filters['kategori'];
                        if (!empty($filters['top']))          $filterTexts[] = 'Top: '        . $filters['top'];
                        if (!empty($filters['petugas_nama'])) $filterTexts[] = 'Petugas: '    . $filters['petugas_nama'];
                    @endphp
                    {{ count($filterTexts) > 0 ? implode(' | ', $filterTexts) : 'Semua Data' }}
                </td>
            </tr>
            <tr>
                <td class="meta-label">Periode</td>
                <td colspan="3">: <strong>{{ !empty($filters['periode']) ? \Carbon\Carbon::createFromFormat('Y-m', $filters['periode'])->locale('id')->translatedFormat('F Y') : 'Semua Periode' }}</strong></td>
            </tr>
        </table>
    </div>

    {{-- Tabel utama data ranking --}}
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th class="center" style="width:40px;">Rank</th>
                    <th style="width:90px;">IDPEL</th>
                    <th>Nama Pelanggan</th>
                    <th style="width:60px;">Unitap</th>
                    <th class="right" title="Historis Tunggakan">C1(Hist)</th>
                    <th class="right" title="Besar Tunggakan">C2(Rp)</th>
                    <th class="right" title="Lama Tunggakan">C3(Bln)</th>
                    <th class="right" title="Total Tagihan">C4(Rp)</th>
                    <th class="right" style="width:70px;">Nilai Vi</th>
                    <th class="center" style="width:80px;">%</th>
                    <th class="center" style="width:100px;">Kategori</th>
                    <th class="center" style="width:60px;">Periode</th>
                </tr>
            </thead>
            <tbody>
                {{-- Looping baris data ranking untuk ditampilkan di PDF --}}
                @if(count($ranking) > 0)
                    @php
                        $max_nilai = $stats['max_nilai'] > 0 ? $stats['max_nilai'] : 1;
                    @endphp
                    @foreach($ranking as $row)
                        @php
                            $pct  = ($row->nilai_akhir / $max_nilai) * 100;
                            $kat  = $row->kategori ?? 'Rendah';
                            $badgeClass = 'bg-rendah';
                            if ($kat === 'Sangat Prioritas') $badgeClass = 'bg-sangat-prioritas';
                            elseif ($kat === 'Prioritas')    $badgeClass = 'bg-prioritas';
                            elseif ($kat === 'Sedang')       $badgeClass = 'bg-sedang';
                        @endphp
                        <tr>
                            <td class="center">{{ $row->ranking }}</td>
                            <td>{{ $row->idpel }}</td>
                            <td>{{ $row->nama ?? '-' }}</td>
                            <td>{{ $row->unitap ?? '-' }}</td>
                            <td class="right">{{ number_format($row->c1 ?? 0, 0, ',', '.') }}</td>
                            <td class="right">{{ number_format($row->c2 ?? 0, 0, ',', '.') }}</td>
                            <td class="right">{{ number_format($row->c3 ?? 0, 0, ',', '.') }}</td>
                            <td class="right">{{ number_format($row->c4 ?? 0, 0, ',', '.') }}</td>
                            <td class="right" style="font-family:monospace; font-size:10pt;">
                                {{ number_format($row->nilai_akhir, 4, ',', '.') }}
                            </td>
                            <td class="center">{{ number_format($pct, 2, ',', '.') }}%</td>
                            <td class="center">
                                <span class="badge {{ $badgeClass }}">{{ $kat }}</span>
                            </td>
                            <td class="center">{{ $row->periode }}</td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="12" class="center" style="padding:20px;">
                            Tidak ada data yang sesuai dengan filter.
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

    {{-- Bagian footer halaman PDF (nomor halaman dan info cetak) --}}
    <div class="footer">
        <table>
            <tr>
                <td>Dicetak oleh sistem pada {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}</td>
                <td style="text-align:right;">Halaman <span class="page-number"></span></td>
            </tr>
        </table>
    </div>

</body>
</html>
