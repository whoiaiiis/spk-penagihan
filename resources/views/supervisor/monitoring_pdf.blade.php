{{-- ======================================================
5. BUAT FILE BARU
resources/views/supervisor/monitoring_pdf.blade.php
====================================================== --}}

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">

    <style>

        body{
            font-family: sans-serif;
            font-size: 11px;
        }

        .title{
            text-align:center;
            margin-bottom:20px;
        }

        .title h2{
            margin:0;
        }

        .summary{
            width:100%;
            margin-bottom:20px;
            border-collapse: collapse;
        }

        .summary td{
            border:1px solid #000;
            padding:6px;
        }

        .table{
            width:100%;
            border-collapse: collapse;
        }

        .table th{
            background:#dbeafe;
        }

        .table th,
        .table td{
            border:1px solid #000;
            padding:5px;
            font-size:10px;
        }

        .text-right{
            text-align:right;
        }

        .text-center{
            text-align:center;
        }

        .baik{
            color:green;
            font-weight:bold;
        }

        .cukup{
            color:orange;
            font-weight:bold;
        }

        .kurang{
            color:red;
            font-weight:bold;
        }

    </style>
</head>

<body>

<div class="title">
    <h2>LAPORAN MONITORING KINERJA PETUGAS</h2>
    <div>PLN SMART COLLECTION</div>

    <br>

    <div>
        Bulan:
        {{ \Carbon\Carbon::createFromFormat('Y-m', $bulan)->translatedFormat('F Y') }}
    </div>

    <div>
        Wilayah:
        {{ $wilayah ?: 'Semua Wilayah' }}
    </div>

    <div>
        Dicetak:
        {{ now()->format('d-m-Y H:i') }}
    </div>
</div>

<table class="summary">
    <tr>
        <td>Total Petugas</td>
        <td>{{ $summary['total_petugas'] }}</td>

        <td>Rata-rata Kinerja</td>
        <td>{{ $summary['avg_kinerja'] }}%</td>
    </tr>

    <tr>
        <td>Sudah Ditagih</td>
        <td>{{ $summary['total_sudah'] }}</td>

        <td>Belum Ditagih</td>
        <td>{{ $summary['total_belum'] }}</td>
    </tr>

    <tr>
        <td>Total RPTAG</td>
        <td>
            Rp {{ number_format($summary['total_rptag'],0,',','.') }}
        </td>

        <td>Total Tertagih</td>
        <td>
            Rp {{ number_format($summary['total_tagih'],0,',','.') }}
        </td>
    </tr>

    <tr>
        <td>Sisa Saldo</td>
        <td colspan="3">
            Rp {{ number_format($summary['total_sisa'],0,',','.') }}
        </td>
    </tr>
</table>

<table class="table">

    <thead>
        <tr>
            <th>NO</th>
            <th>NAMA PETUGAS</th>
            <th>WILAYAH</th>
            <th>TARGET</th>
            <th>SUDAH</th>
            <th>BELUM</th>
            <th>%</th>
            <th>RPTAG</th>
            <th>TERTAGIH</th>
            <th>SISA</th>
            <th>STATUS</th>
        </tr>
    </thead>

    <tbody>

        @foreach($kinerja as $i => $p)

        <tr>

            <td class="text-center">
                {{ $i + 1 }}
            </td>

            <td>
                {{ $p->nama }}
            </td>

            <td>
                {{ $p->wilayah }}
            </td>

            <td class="text-right">
                {{ number_format($p->total_ditagih) }}
            </td>

            <td class="text-right">
                {{ number_format($p->sudah_tagih) }}
            </td>

            <td class="text-right">
                {{ number_format($p->belum_tagih) }}
            </td>

            <td class="text-center">
                {{ $p->persen }}%
            </td>

            <td class="text-right">
                {{ number_format($p->total_rptag,0,',','.') }}
            </td>

            <td class="text-right">
                {{ number_format($p->total_tagih,0,',','.') }}
            </td>

            <td class="text-right">
                {{ number_format($p->sisa_saldo,0,',','.') }}
            </td>

            <td class="text-center">

                <span class="{{ strtolower($p->kategori) }}">
                    {{ $p->kategori }}
                </span>

            </td>

        </tr>

        @endforeach

    </tbody>

    <tfoot>

        <tr>

            <th colspan="3">
                TOTAL
            </th>

            <th>
                {{ number_format($summary['total_target']) }}
            </th>

            <th>
                {{ number_format($summary['total_sudah']) }}
            </th>

            <th>
                {{ number_format($summary['total_belum']) }}
            </th>

            <th>
                {{ $summary['avg_kinerja'] }}%
            </th>

            <th>
                {{ number_format($summary['total_rptag'],0,',','.') }}
            </th>

            <th>
                {{ number_format($summary['total_tagih'],0,',','.') }}
            </th>

            <th>
                {{ number_format($summary['total_sisa'],0,',','.') }}
            </th>

            <th></th>

        </tr>

    </tfoot>

</table>

</body>
</html>