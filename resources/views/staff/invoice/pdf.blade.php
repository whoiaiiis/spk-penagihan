{{-- C:\laragon\www\spk-penagihan\resources\views\staff\invoice\pdf.blade.php --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Invoice Tagihan PLN</title>
    <style>

        * { margin: 0; padding: 0; box-sizing: border-box; }

        @page {
            size: A4 portrait;
            margin: 5mm;
        }

        body {
            font-family: 'Arial', sans-serif;
            background: #fff;
            color: #000;
            line-height: 1.2;
            -webkit-print-color-adjust: exact;
        }

        .page {
            width: 200mm;     
            height: 287mm;    
            background: #fff;
            page-break-after: always;
            display: flex;
            flex-direction: column;
            position: relative;
            margin: 0 auto;
        }

        .invoice-pair {
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .invoice {
            height: 138mm;
            padding: 6mm 10mm;
            border: 1px dashed #666;
            display: flex;
            flex-direction: column;
            page-break-inside: avoid;
            overflow: hidden;
            box-sizing: border-box;
        }

        .divider {
            height: 8mm;
            margin: 0;
            border: none;
            background: transparent;
        }

        .invoice + .invoice {
            margin-top: 8mm;
        }

        /* HEADER SECTION */
        .invoice-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 6pt; border-bottom: 2pt solid #005baa; padding-bottom: 4pt; }
        .logo-section { display: flex; align-items: center; gap: 8pt; }
        .logo-text h2 { font-size: 11pt; font-weight: bold; color: #005baa; margin: 0; line-height: 1.1; }
        .logo-text p { font-size: 7pt; color: #666; margin: 0; }
        .nomor-section { text-align: right; }
        .nomor-section label { font-size: 7pt; color: #666; text-transform: uppercase; font-weight: bold; }
        .nomor-section .value { font-size: 9.5pt; font-weight: bold; color: #000; font-family: 'Courier New', monospace; margin-top: 2pt; }

        /* TITLE SECTION */
        .title-section { text-align: center; margin-bottom: 6pt; }
        .title-section h3 { font-size: 9pt; font-weight: bold; color: #000; margin: 0; }
        .title-section p { font-size: 7.5pt; font-style: italic; color: #d9730d; margin: 2pt 0 0 0; }

        /* WARNING SECTION */
        .warning-section { background: #fff3cd; border: 1pt solid #ffc107; border-radius: 2pt; padding: 4pt 6pt; margin-bottom: 6pt; display: flex; gap: 6pt; align-items: center; }
        .warning-icon { font-size: 11pt; color: #ff9800; line-height: 1; margin-top: -2pt; }
        .warning-text { font-size: 7pt; color: #d9730d; line-height: 1.3; font-weight: bold; }

        /* INVOICE DETAILS */
        .invoice-body { flex: 1; display: flex; flex-direction: column; gap: 4pt; }
        .detail-table { width: 100%; font-size: 8pt; margin-bottom: 4pt; }
        .detail-table td { padding: 2pt 4pt; vertical-align: top; }
        .detail-table td:nth-child(1), .detail-table td:nth-child(4) { width: 18%; color: #666; font-weight: bold; font-size: 7pt; text-transform: uppercase; }
        .detail-table td:nth-child(2), .detail-table td:nth-child(5) { width: 2%; }
        .detail-table td:nth-child(3), .detail-table td:nth-child(6) { width: 30%; font-weight: bold; }
        .detail-table .mono { font-family: 'Courier New', monospace; }
        .detail-table .big { font-size: 9.5pt; color: #005baa; }

        /* TAGIHAN SECTION */
        .tagihan-section { border-top: 1pt solid #ddd; border-bottom: 2pt solid #005baa; padding: 4pt 0; margin: 4pt 0; }
        .tagihan-row { display: flex; justify-content: space-between; align-items: center; font-size: 8.5pt; padding: 2pt 0; border-bottom: 1pt dotted #ddd; }
        .tagihan-row:last-child { border-bottom: none; }
        .tagihan-label { color: #666; }
        .tagihan-value { font-weight: bold; font-family: 'Courier New', monospace; color: #000; }
        .tagihan-total { display: flex; justify-content: space-between; font-size: 9.5pt; font-weight: bold; padding: 4pt 0; margin-top: 2pt; border-top: 1pt solid #000; }
        .tagihan-total .label { color: #005baa; }
        .tagihan-total .value { color: #005baa; font-family: 'Courier New', monospace; }

        /* EXTRA INFO SECTION */
        .extra-info { font-size: 7pt; color: #333; text-align: justify; line-height: 1.3; margin-top: 4pt; }
        .extra-info p { margin-bottom: 4pt; }
        .extra-info strong { font-size: 8pt; }

        /* SIGNATURE SECTION */
        .signature-section { display: flex; justify-content: space-between; margin-top: auto; padding-top: 4pt; border-top: 1pt solid #ddd; }
        
        .tembusan-box { font-size: 7pt; color: #333; width: 40%; }
        .tembusan-box strong { display: block; margin-bottom: 2pt; }
        
        .signature-box { display: flex; flex-direction: column; align-items: center; text-align: center; width: 30%; }
        .signature-label { font-size: 7.5pt; color: #000; margin-bottom: 20pt; }
        .signature-name { font-size: 7.5pt; font-weight: bold; color: #000; border-bottom: 1px solid #000; display: inline-block; padding-bottom: 1pt; }

        /* FOOTER NOTE */
        .footer-note { font-size: 7pt; color: #999; text-align: center; margin-top: 4pt; border-top: 1pt solid #e0e0e0; padding-top: 2pt; line-height: 1.2; }

        @media print {
            body { background: #fff; margin: 0; padding: 0; }
            .page { box-shadow: none; margin: 0; padding: 0; width: 100%; height: 100%; page-break-after: always; }
            .invoice { border: 1px dashed #666; }
        }
        @media screen {
            body { background: #f0f0f0; padding: 20px 0; display: flex; flex-direction: column; align-items: center; gap: 20px; }
            .page { box-shadow: 0 4px 16px rgba(0,0,0,0.1); }
            .invoice { border: 1px dashed #ddd; background: #fff; }
        }

    </style>
</head>
<body>

{{-- Looping per 2 invoice untuk dicetak di satu halaman (kertas A4 dibagi 2) --}}
@foreach($invoice->chunk(2) as $pair)
    <div class="page">
        <div class="invoice-pair">


        @if(isset($pair[0]))
        <div class="invoice">
            {{-- Header invoice --}}
            <div class="invoice-header">
                <div class="logo-section">
                    <img src="{{ public_path('images/logo_pln.png') }}" alt="Logo PLN" style="height: 30pt; width: auto;">
                    <div class="logo-text">
                        <h2>PT PLN (Persero) UID S2JB</h2>
                        <p>UP3 Palembang - ULP RIVAI</p>
                    </div>
                </div>
                <div class="nomor-section">
                    <label>Nomor Surat</label>
                    <div class="value">{{ $pair[0]->nomor_surat }}</div>
                </div>
            </div>

            <div class="title-section">
                <h3>PEMBERITAHUAN INFORMASI TAGIHAN REKENING LISTRIK</h3>
                <p>(ABAIKAN SURAT INI APABILA SUDAH DILAKUKAN PEMBAYARAN)</p>
            </div>

            {{-- Warning Section --}}
            <div class="warning-section">
                <div class="warning-icon">⚠</div>
                <div class="warning-text">
                    BAYARLAH SEBELUM JATUH TEMPO. Keterlambatan pembayaran dapat mengakibatkan pengenaan denda atau pemutusan layanan.
                </div>
            </div>

            {{-- Invoice Body --}}
            <div class="invoice-body">
                {{-- Detail Pelanggan dengan Table agar rapi di dompdf --}}
                <table class="detail-table">
                    <tr>
                        <td>ID Pelanggan</td>
                        <td>:</td>
                        <td class="mono">{{ $pair[0]->idpel }}</td>
                        
                        <td>Daya / Tarif</td>
                        <td>:</td>
                        <td class="big">{{ number_format($pair[0]->daya) }} VA / R1</td>
                    </tr>
                    <tr>
                        <td>Nama Pelanggan</td>
                        <td>:</td>
                        <td>{{ $pair[0]->nama }}</td>
                        
                        <td>Periode</td>
                        <td>:</td>
                        <td>{{ $pair[0]->bulan }}</td>
                    </tr>
                    <tr>
                        <td>Petugas</td>
                        <td>:</td>
                        <td>{{ $pair[0]->nama_petugas ?: '-' }} ({{ $pair[0]->kode_petugas ?: '-' }})</td>
                        
                        <td>Ranking / SAW</td>
                        <td>:</td>
                        <td class="big">{{ $pair[0]->ranking }} / {{ number_format($pair[0]->nilai_akhir, 6) }}</td>
                    </tr>
                </table>

                {{-- Tagihan Details --}}
                <div class="tagihan-section">
                    <div class="tagihan-row">
                        <span class="tagihan-label">Biaya Tagihan (RPTAG)</span>
                        <span class="tagihan-value">Rp {{ number_format($pair[0]->rptag, 0, ',', '.') }}</span>
                    </div>
                    <div class="tagihan-row">
                        <span class="tagihan-label">Biaya Beban Kerja (RPBK)</span>
                        <span class="tagihan-value">Rp {{ number_format($pair[0]->rpbk, 0, ',', '.') }}</span>
                    </div>
                    <div class="tagihan-total">
                        <span class="label">TOTAL TAGIHAN</span>
                        <span class="value">Rp {{ number_format($pair[0]->total_tagihan, 0, ',', '.') }},-</span>
                    </div>
                </div>

                {{-- Extra Info --}}
                <div class="extra-info">
                    <p>Melalui Surat pemberitahuan ini, Kami himbau agar para Pelanggan PLN dapat Disiplin membayar tagihan listrik tepat waktu sebelum tanggal <strong>20</strong> setiap bulannya untuk menghindari sanksi PEMUTUSAN SEMENTARA (SEGEL MCB / PUTUS KABEL / BONGKAR KWH DAN GANTI PRABAYAR) sesuai peraturan yang berlaku.</p>
                    <p>Untuk informasi tagihan listrik bulanan silakan download PLN MOBILE. Demikian disampaikan, atas perhatiannya Kami ucapkan terima kasih.</p>
                </div>
            </div>

            {{-- Signature Section --}}
            <div class="signature-section">
                <div class="tembusan-box">
                    <strong>TEMBUSAN :</strong>
                    Yth. KEPALA KEJAKSAAN NEGERI<br>
                    PALEMBANG
                </div>
                
                <div class="signature-box" style="margin-left: auto;">
                    <div class="signature-label">
                        Palembang, {{ strtoupper(\Carbon\Carbon::parse($pair[0]->tanggal_cetak ?: now())->translatedFormat('F Y')) }}<br>
                        Manajer
                    </div>
                    <div class="signature-name">ASYAUQI PUTRA IZZATI</div>
                </div>
            </div>

            {{-- Footer Note --}}
            <div class="footer-note">
                Dokumen ini dicetak oleh Sistem Penunjang Keputusan PLN pada {{ now()->format('d/m/Y H:i') }}
            </div>
        </div>
        @endif


            {{-- Pemisah antara dua invoice --}}
            @if(isset($pair[1]))
            <div class="divider"></div>
            @endif


        @if(isset($pair[1]))
        <div class="invoice">
            {{-- Header invoice --}}
            <div class="invoice-header">
                <div class="logo-section">
                    <img src="{{ public_path('images/logo_pln.png') }}" alt="Logo PLN" style="height: 30pt; width: auto;">
                    <div class="logo-text">
                        <h2>PT PLN (Persero) UID S2JB</h2>
                        <p>UP3 Palembang - ULP RIVAI</p>
                    </div>
                </div>
                <div class="nomor-section">
                    <label>Nomor Surat</label>
                    <div class="value">{{ $pair[1]->nomor_surat }}</div>
                </div>
            </div>

            <div class="title-section">
                <h3>PEMBERITAHUAN INFORMASI TAGIHAN REKENING LISTRIK</h3>
                <p>(ABAIKAN SURAT INI APABILA SUDAH DILAKUKAN PEMBAYARAN)</p>
            </div>

            {{-- Warning Section --}}
            <div class="warning-section">
                <div class="warning-icon">⚠</div>
                <div class="warning-text">
                    BAYARLAH SEBELUM JATUH TEMPO. Keterlambatan pembayaran dapat mengakibatkan pengenaan denda atau pemutusan layanan.
                </div>
            </div>

            {{-- Invoice Body --}}
            <div class="invoice-body">
                {{-- Detail Pelanggan dengan Table agar rapi di dompdf --}}
                <table class="detail-table">
                    <tr>
                        <td>ID Pelanggan</td>
                        <td>:</td>
                        <td class="mono">{{ $pair[1]->idpel }}</td>
                        
                        <td>Daya / Tarif</td>
                        <td>:</td>
                        <td class="big">{{ number_format($pair[1]->daya) }} VA / R1</td>
                    </tr>
                    <tr>
                        <td>Nama Pelanggan</td>
                        <td>:</td>
                        <td>{{ $pair[1]->nama }}</td>
                        
                        <td>Periode</td>
                        <td>:</td>
                        <td>{{ $pair[1]->bulan }}</td>
                    </tr>
                    <tr>
                        <td>Petugas</td>
                        <td>:</td>
                        <td>{{ $pair[1]->nama_petugas ?: '-' }} ({{ $pair[1]->kode_petugas ?: '-' }})</td>
                        
                        <td>Ranking / SAW</td>
                        <td>:</td>
                        <td class="big">{{ $pair[1]->ranking }} / {{ number_format($pair[1]->nilai_akhir, 6) }}</td>
                    </tr>
                </table>

                {{-- Tagihan Details --}}
                <div class="tagihan-section">
                    <div class="tagihan-row">
                        <span class="tagihan-label">Biaya Tagihan (RPTAG)</span>
                        <span class="tagihan-value">Rp {{ number_format($pair[1]->rptag, 0, ',', '.') }}</span>
                    </div>
                    <div class="tagihan-row">
                        <span class="tagihan-label">Biaya Beban Kerja (RPBK)</span>
                        <span class="tagihan-value">Rp {{ number_format($pair[1]->rpbk, 0, ',', '.') }}</span>
                    </div>
                    <div class="tagihan-total">
                        <span class="label">TOTAL TAGIHAN</span>
                        <span class="value">Rp {{ number_format($pair[1]->total_tagihan, 0, ',', '.') }},-</span>
                    </div>
                </div>

                {{-- Extra Info --}}
                <div class="extra-info">
                    <p>Melalui Surat pemberitahuan ini, Kami himbau agar para Pelanggan PLN dapat Disiplin membayar tagihan listrik tepat waktu sebelum tanggal <strong>20</strong> setiap bulannya untuk menghindari sanksi PEMUTUSAN SEMENTARA (SEGEL MCB / PUTUS KABEL / BONGKAR KWH DAN GANTI PRABAYAR) sesuai peraturan yang berlaku.</p>
                    <p>Untuk informasi tagihan listrik bulanan silakan download PLN MOBILE. Demikian disampaikan, atas perhatiannya Kami ucapkan terima kasih.</p>
                </div>
            </div>

            {{-- Signature Section --}}
            <div class="signature-section">
                <div class="tembusan-box">
                    <strong>TEMBUSAN :</strong>
                    Yth. KEPALA KEJAKSAAN NEGERI<br>
                    PALEMBANG
                </div>
                
                <div class="signature-box" style="margin-left: auto;">
                    <div class="signature-label">
                        Palembang, {{ strtoupper(\Carbon\Carbon::parse($pair[1]->tanggal_cetak ?: now())->translatedFormat('F Y')) }}<br>
                        Manajer
                    </div>
                    <div class="signature-name">ASYAUQI PUTRA IZZATI</div>
                </div>
            </div>

            {{-- Footer Note --}}
            <div class="footer-note">
                Dokumen ini dicetak oleh Sistem Penunjang Keputusan PLN pada {{ now()->format('d/m/Y H:i') }}
            </div>
        </div>
        @endif


        </div>
    </div>
@endforeach
</body>
</html>
