{{-- resources/views/staff/invoice/print.blade.php --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
            width: 200mm;      /* A4 Width(210) - 2x5mm margin = 200mm */
            height: 287mm;     /* A4 Height(297) - 2x5mm margin = 287mm */
            margin: 0 auto;
            background: #fff;
            page-break-after: always;
            display: flex;
            flex-direction: column;
            position: relative;
        }

        .invoice-pair {
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .invoice {
            height: 138mm; /* 2 * 138 = 276mm, fits perfectly within 287mm with a small gap */
            padding: 8mm 12mm;
            border: 1px dashed #666; /* clearer boundary */
            display: flex;
            flex-direction: column;
            page-break-inside: avoid;
            overflow: hidden;
            box-sizing: border-box;
        }

        .divider {
            height: 8mm; /* Gap between invoice 1 and 2 */
            margin: 0;
            border: none;
            background: transparent;
        }

        .invoice + .invoice {
            margin-top: 8mm; /* For pdf.blade.php where .divider might not exist */
        }

        /* ================================================================
           HEADER SECTION
        ================================================================ */
        .invoice-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8pt; border-bottom: 2pt solid #005baa; padding-bottom: 6pt; }
        .logo-section { display: flex; align-items: center; gap: 8pt; }
        .logo-box { width: 34pt; height: 34pt; background: #005baa; color: #fff; display: flex; align-items: center; justify-content: center; border-radius: 3pt; font-weight: bold; font-size: 16pt; }
        .logo-text h2 { font-size: 12pt; font-weight: bold; color: #005baa; margin: 0; line-height: 1.1; }
        .logo-text p { font-size: 7.5pt; color: #666; margin: 0; }
        .nomor-section { text-align: right; }
        .nomor-section label { font-size: 7.5pt; color: #666; text-transform: uppercase; font-weight: bold; }
        .nomor-section .value { font-size: 10.5pt; font-weight: bold; color: #000; font-family: 'Courier New', monospace; margin-top: 2pt; }

        /* ================================================================
           WARNING SECTION
        ================================================================ */
        .warning-section { background: #fff3cd; border: 1pt solid #ffc107; border-radius: 2pt; padding: 6pt; margin-bottom: 8pt; display: flex; gap: 6pt; align-items: center; }
        .warning-icon { font-size: 12pt; color: #ff9800; line-height: 1; margin-top: -2pt; }
        .warning-text { font-size: 7.5pt; color: #d9730d; line-height: 1.3; font-weight: bold; }

        /* ================================================================
           INVOICE DETAILS
        ================================================================ */
        .invoice-body { flex: 1; display: flex; flex-direction: column; gap: 6pt; }
        .detail-section { display: grid; grid-template-columns: 1fr 1fr; gap: 6pt 16pt; }
        .detail-group { display: flex; flex-direction: column; gap: 2pt; }
        .detail-label { font-size: 7.5pt; color: #666; text-transform: uppercase; font-weight: bold; letter-spacing: 0.3pt; }
        .detail-value { font-size: 9.5pt; font-weight: bold; color: #000; }
        .detail-value.mono { font-family: 'Courier New', monospace; font-size: 8.5pt; }
        .detail-value.big { font-size: 10.5pt; color: #005baa; }
        .detail-group.full { grid-column: 1 / -1; }

        /* ================================================================
           TAGIHAN SECTION
        ================================================================ */
        .tagihan-section { border-top: 1pt solid #ddd; border-bottom: 2pt solid #005baa; padding: 6pt 0; margin: 6pt 0; }
        .tagihan-row { display: flex; justify-content: space-between; align-items: center; font-size: 9pt; padding: 3pt 0; border-bottom: 1pt dotted #ddd; }
        .tagihan-row:last-child { border-bottom: none; }
        .tagihan-label { color: #666; }
        .tagihan-value { font-weight: bold; font-family: 'Courier New', monospace; color: #000; }
        .tagihan-total { display: flex; justify-content: space-between; font-size: 10pt; font-weight: bold; padding: 4pt 0; margin-top: 4pt; border-top: 1pt solid #000; }
        .tagihan-total .label { color: #005baa; }
        .tagihan-total .value { color: #005baa; font-family: 'Courier New', monospace; }

        /* ================================================================
           SIGNATURE SECTION
        ================================================================ */
        .signature-section { display: flex; justify-content: space-between; margin-top: auto; padding-top: 6pt; border-top: 1pt solid #ddd; }
        .signature-box { display: flex; flex-direction: column; align-items: center; gap: 24pt; text-align: center; min-width: 80pt; }
        .signature-label { font-weight: bold; color: #000; font-size: 8pt; }
        .signature-name { font-size: 8pt; color: #666; font-style: italic; }

        /* ================================================================
           FOOTER NOTE
        ================================================================ */
        .footer-note { font-size: 7.5pt; color: #999; text-align: center; margin-top: 6pt; border-top: 1pt solid #e0e0e0; padding-top: 4pt; line-height: 1.2; }

        @media print {
            body { background: #fff; margin: 0; padding: 0; }
            .page { box-shadow: none; margin: 0; padding: 0; width: 100%; height: 100%; page-break-after: always; }
            .invoice { border: 1px dashed #666; }
        }
        @media screen {
            body { background: #f0f0f0; padding: 20px 0; display: flex; flex-direction: column; align-items: center; gap: 20px; }
            .page { box-shadow: 0 4px 16px rgba(0,0,0,0.1); }
        }
    </style>
</head>
<body>

{{-- Loop through invoices in pairs --}}
@foreach($invoice->chunk(2) as $pair)
    <div class="page">
        <div class="invoice-pair">

            {{-- First Invoice --}}
            @if(isset($pair[0]))
            <div class="invoice">
                {{-- Header --}}
                <div class="invoice-header">
                    <div class="logo-section">
                        <div class="logo-box">PLN</div>
                        <div class="logo-text">
                            <h2>PLN Tagihan</h2>
                            <p>Sistem Penunjang Keputusan</p>
                        </div>
                    </div>
                    <div class="nomor-section">
                        <label>Nomor Surat</label>
                        <div class="value">{{ $pair[0]->nomor_surat }}</div>
                    </div>
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
                    {{-- Detail Pelanggan --}}
                    <div class="detail-section">
                        <div class="detail-group">
                            <div class="detail-label">ID Pelanggan (IDPEL)</div>
                            <div class="detail-value mono">{{ $pair[0]->idpel }}</div>
                        </div>
                        <div class="detail-group">
                            <div class="detail-label">Nama Pelanggan</div>
                            <div class="detail-value">{{ $pair[0]->nama }}</div>
                        </div>
                        <div class="detail-group">
                            <div class="detail-label">Petugas & RBM</div>
                            <div class="detail-value">{{ $pair[0]->nama_petugas ?: '-' }} ({{ $pair[0]->rbm ?: '-' }})</div>
                        </div>
                        <div class="detail-group">
                            <div class="detail-label">Daya Terpasang (VA)</div>
                            <div class="detail-value big">{{ number_format($pair[0]->daya) }}</div>
                        </div>
                        <div class="detail-group">
                            <div class="detail-label">Periode Tagihan</div>
                            <div class="detail-value">{{ $pair[0]->bulan }}</div>
                        </div>
                    </div>

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
                            <span class="value">Rp {{ number_format($pair[0]->total_tagihan, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    {{-- Ranking & SAW --}}
                    <div class="detail-section">
                        <div class="detail-group">
                            <div class="detail-label">Ranking</div>
                            <div class="detail-value big">{{ $pair[0]->ranking }}</div>
                        </div>
                        <div class="detail-group">
                            <div class="detail-label">Nilai Akhir (SAW)</div>
                            <div class="detail-value big">{{ number_format($pair[0]->nilai_akhir, 6) }}</div>
                        </div>
                    </div>
                </div>

                {{-- Signature Section --}}
                <div class="signature-section">
                    <div class="signature-box" style="margin-right: auto;">
                        <div>
                            <div class="signature-label">Pemberi Tagihan</div>
                            <div class="signature-name">PT. PLN (Persero)</div>
                        </div>
                    </div>
                    <div class="signature-box" style="margin-left: auto;">
                        <div>
                            <div class="signature-label">Diketahui,</div>
                            <div class="signature-name">Manajer Operasional</div>
                        </div>
                    </div>
                </div>

                {{-- Footer Note --}}
                <div class="footer-note">
                    Dokumen ini dicetak oleh Sistem Penunjang Keputusan PLN pada {{ now()->format('d/m/Y H:i') }}<br>
                    Harap simpan dokumen ini sebagai bukti penerimaan tagihan
                </div>
            </div>
            @endif

            {{-- Divider --}}
            @if(isset($pair[1]))
            <div class="divider"></div>
            @endif

            {{-- Second Invoice --}}
            @if(isset($pair[1]))
            <div class="invoice">
                {{-- Header --}}
                <div class="invoice-header">
                    <div class="logo-section">
                        <div class="logo-box">PLN</div>
                        <div class="logo-text">
                            <h2>PLN Tagihan</h2>
                            <p>Sistem Penunjang Keputusan</p>
                        </div>
                    </div>
                    <div class="nomor-section">
                        <label>Nomor Surat</label>
                        <div class="value">{{ $pair[1]->nomor_surat }}</div>
                    </div>
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
                    {{-- Detail Pelanggan --}}
                    <div class="detail-section">
                        <div class="detail-group">
                            <div class="detail-label">ID Pelanggan (IDPEL)</div>
                            <div class="detail-value mono">{{ $pair[1]->idpel }}</div>
                        </div>
                        <div class="detail-group">
                            <div class="detail-label">Nama Pelanggan</div>
                            <div class="detail-value">{{ $pair[1]->nama }}</div>
                        </div>
                        <div class="detail-group">
                            <div class="detail-label">Petugas & RBM</div>
                            <div class="detail-value">{{ $pair[1]->nama_petugas ?: '-' }} ({{ $pair[1]->rbm ?: '-' }})</div>
                        </div>
                        <div class="detail-group">
                            <div class="detail-label">Daya Terpasang (VA)</div>
                            <div class="detail-value big">{{ number_format($pair[1]->daya) }}</div>
                        </div>
                        <div class="detail-group">
                            <div class="detail-label">Periode Tagihan</div>
                            <div class="detail-value">{{ $pair[1]->bulan }}</div>
                        </div>
                    </div>

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
                            <span class="value">Rp {{ number_format($pair[1]->total_tagihan, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    {{-- Ranking & SAW --}}
                    <div class="detail-section">
                        <div class="detail-group">
                            <div class="detail-label">Ranking</div>
                            <div class="detail-value big">{{ $pair[1]->ranking }}</div>
                        </div>
                        <div class="detail-group">
                            <div class="detail-label">Nilai Akhir (SAW)</div>
                            <div class="detail-value big">{{ number_format($pair[1]->nilai_akhir, 6) }}</div>
                        </div>
                    </div>
                </div>

                {{-- Signature Section --}}
                <div class="signature-section">
                    <div class="signature-box" style="margin-right: auto;">
                        <div>
                            <div class="signature-label">Pemberi Tagihan</div>
                            <div class="signature-name">PT. PLN (Persero)</div>
                        </div>
                    </div>
                    <div class="signature-box" style="margin-left: auto;">
                        <div>
                            <div class="signature-label">Diketahui,</div>
                            <div class="signature-name">Manajer Operasional</div>
                        </div>
                    </div>
                </div>

                {{-- Footer Note --}}
                <div class="footer-note">
                    Dokumen ini dicetak oleh Sistem Penunjang Keputusan PLN pada {{ now()->format('d/m/Y H:i') }}<br>
                    Harap simpan dokumen ini sebagai bukti penerimaan tagihan
                </div>
            </div>
            @endif

        </div>
    </div>
@endforeach

<script>
    window.addEventListener('load', function() {
        window.print();
        window.setTimeout(function() {
            window.close();
        }, 250);
    });
</script>

</body>
</html>
