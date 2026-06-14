<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Stock Opname — Alex Phone Banjarnegara</title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 9pt;
    color: #0A2540;
    background: #fff;
}
.page { padding: 20px 28px; }

/* ── HEADER ── */
.header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2.5px solid #0A2540; padding-bottom: 10px; margin-bottom: 12px; }
.store-name { font-size: 16pt; font-weight: 800; letter-spacing: -0.3px; color: #0A2540; }
.store-sub  { font-size: 8pt; color: #3D5374; margin-top: 2px; }
.doc-label  { font-size: 13pt; font-weight: 700; color: #0A2540; text-align: right; letter-spacing: 0.5px; text-transform: uppercase; }
.doc-meta   { font-size: 7.5pt; color: #3D5374; margin-top: 3px; text-align: right; }

/* ── KPI ROW ── */
.kpi-row { display: flex; gap: 8px; margin-bottom: 12px; }
.kpi { flex: 1; border: 1px solid #E4E9F2; border-top: 3px solid #0A2540; padding: 8px 10px; }
.kpi-label { font-size: 7pt; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; color: #7A8AA8; margin-bottom: 3px; }
.kpi-value { font-size: 12pt; font-weight: 700; color: #0A2540; }
.kpi-sub   { font-size: 7pt; color: #3D5374; margin-top: 2px; }

/* ── SECTION TITLE ── */
.section-title {
    font-size: 8pt; font-weight: 700; text-transform: uppercase;
    letter-spacing: 0.8px; color: #fff;
    background: #0A2540;
    padding: 5px 10px;
    margin-top: 14px; margin-bottom: 0;
    display: flex; justify-content: space-between; align-items: center;
}
.section-title .section-count { font-size: 7pt; font-weight: 400; opacity: 0.75; }

/* ── TABLE ── */
table { width: 100%; border-collapse: collapse; font-size: 7.5pt; }
thead tr { background: #1E3A5F; color: #fff; }
thead th { padding: 5px 7px; text-align: left; font-weight: 700; font-size: 7pt; text-transform: uppercase; letter-spacing: 0.4px; white-space: nowrap; }
thead th.r { text-align: right; }
thead th.c { text-align: center; }
tbody tr { border-bottom: 1px solid #E4E9F2; }
tbody tr:nth-child(even) { background: #F7F9FC; }
tbody td { padding: 5px 7px; color: #0A2540; vertical-align: middle; }
tbody td.r { text-align: right; font-family: 'Courier New', monospace; }
tbody td.c { text-align: center; }
tbody td.muted { color: #7A8AA8; font-size: 7pt; }

/* blank fill columns */
.fill-cell { border-bottom: 1px solid #9CA3AF !important; min-width: 52px; background: #FFFBEB !important; }
.fill-cell-wide { border-bottom: 1px solid #9CA3AF !important; min-width: 80px; background: #FFFBEB !important; }

/* badges */
.badge { display: inline-block; padding: 1px 5px; border-radius: 2px; font-size: 7pt; font-weight: 700; }
.badge-ready  { background: #ECFDF5; color: #065F46; border: 1px solid #A7F3D0; }
.badge-sold   { background: #F1F5F9; color: #475569; border: 1px solid #CBD5E1; }
.badge-retur  { background: #FEF2F2; color: #B91C1C; border: 1px solid #FECACA; }
.badge-warn   { background: #FEF3C7; color: #92400E; border: 1px solid #FDE68A; }

/* total row */
.total-row td { background: #E8EDF5 !important; font-weight: 700; border-top: 2px solid #0A2540; }

/* brand row */
.brand-row td { background: #E8EDF5 !important; font-weight: 700; border-top: 1.5px solid #0A2540; border-bottom: 1.5px solid #0A2540; color: #0A2540; }

/* ── LEGEND ── */
.legend { margin-top: 10px; display: flex; gap: 20px; font-size: 7.5pt; color: #3D5374; }
.legend-dot { display: inline-block; width: 10px; height: 10px; border-radius: 2px; margin-right: 4px; vertical-align: middle; }

/* ── SIGNATURE BLOCK ── */
.sig-section { margin-top: 24px; border-top: 1px solid #E4E9F2; padding-top: 14px; }
.sig-title { font-size: 8pt; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; color: #0A2540; margin-bottom: 14px; }
.sig-row { display: flex; gap: 20px; }
.sig-box { flex: 1; border: 1px solid #CBD5E1; border-radius: 4px; padding: 10px 12px; }
.sig-label { font-size: 7pt; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #7A8AA8; margin-bottom: 4px; }
.sig-name-line { border-bottom: 1px solid #0A2540; height: 32px; margin-bottom: 4px; }
.sig-role  { font-size: 7.5pt; color: #3D5374; }
.sig-date-box { border: 1px solid #CBD5E1; border-radius: 4px; padding: 10px 12px; min-width: 180px; }
.sig-date-line { border-bottom: 1px solid #0A2540; height: 32px; margin: 4px 0; }
.check-note { font-size: 7pt; color: #6B7280; margin-top: 4px; font-style: italic; }

/* ── FOOTER ── */
.footer { margin-top: 14px; border-top: 1px solid #E4E9F2; padding-top: 6px; display: flex; justify-content: space-between; font-size: 7pt; color: #9CA3AF; font-style: italic; }

/* ── CHECKLIST COLUMN ── */
.cb-cell { text-align: center; width: 20px; padding: 4px 3px !important; }
.cb-box {
    display: inline-block; width: 13px; height: 13px;
    border: 1.5px solid #374151; border-radius: 2px; vertical-align: middle;
    background: #fff;
}

/* ── PRINT-ONLY BUTTON ── */
.no-print-bar {
    background: #1E3A5F; color: #fff; padding: 10px 28px;
    display: flex; align-items: center; justify-content: space-between;
}
.no-print-bar .bar-title { font-size: 11pt; font-weight: 700; letter-spacing: 0.2px; }
.no-print-bar .bar-meta  { font-size: 8pt; opacity: 0.7; }
.print-btn {
    background: #fff; color: #1E3A5F; border: none; cursor: pointer;
    padding: 7px 20px; border-radius: 4px; font-size: 10pt; font-weight: 700;
    display: flex; align-items: center; gap: 6px;
}
.print-btn:hover { background: #E0E7FF; }
.back-link { color: #93C5FD; font-size: 8.5pt; text-decoration: none; display: flex; align-items: center; gap-4px; }
.back-link:hover { color: #BFDBFE; }

@media print {
    .no-print-bar { display: none !important; }
    body { padding: 0; }
    .page { padding: 16px 22px; }
    @page { size: A4 landscape; margin: 10mm; }
}
</style>
</head>
<body>

{{-- Top action bar (hidden on print) --}}
<div class="no-print-bar">
    <div>
        <div class="bar-title">Stock Opname — Alex Phone Banjarnegara</div>
        <div class="bar-meta">Digenerate: {{ $printedAt }}</div>
    </div>
    <div style="display:flex;align-items:center;gap:16px">
        <a href="{{ route('reports.stock') }}" class="back-link">
            ← Kembali ke Laporan Stok
        </a>
        <button onclick="window.print()" class="print-btn">
            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            Cetak
        </button>
    </div>
</div>

<div class="page">

  {{-- DOCUMENT HEADER --}}
  <div class="header">
    <div>
      <div class="store-name">ALEX PHONE BANJARNEGARA</div>
      <div class="store-sub">Pusat Penjualan &amp; Servis Smartphone &mdash; Banjarnegara, Jawa Tengah &nbsp;|&nbsp; WA: 0896-7488-6141</div>
    </div>
    <div>
      <div class="doc-label">Lembar Stock Opname</div>
      <div class="doc-meta">Dicetak: {{ $printedAt }}</div>
    </div>
  </div>

  {{-- KPI SUMMARY --}}
  <div class="kpi-row">
    <div class="kpi">
      <div class="kpi-label">Total Unit HP</div>
      <div class="kpi-value">{{ $units->count() }} unit</div>
      <div class="kpi-sub">Ready: {{ $readyCount }} &nbsp;|&nbsp; Retur: {{ $units->count() - $readyCount }}</div>
    </div>
    <div class="kpi">
      <div class="kpi-label">Nilai Modal Stok Ready</div>
      <div class="kpi-value">Rp {{ number_format($assetModal, 0, ',', '.') }}</div>
      <div class="kpi-sub">Berdasarkan harga beli unit ready</div>
    </div>
    <div class="kpi">
      <div class="kpi-label">Aksesoris</div>
      <div class="kpi-value">{{ $accessories->count() }} jenis</div>
      <div class="kpi-sub">Total qty: {{ number_format($accQty, 0, ',', '.') }} pcs &nbsp;|&nbsp; Modal: Rp {{ number_format($accModal, 0, ',', '.') }}</div>
    </div>
    <div class="kpi" style="border-top-color:#7E22CE">
      <div class="kpi-label">Status Lembar</div>
      <div class="kpi-value" style="font-size:10pt;color:#7E22CE">Belum Diverifikasi</div>
      <div class="kpi-sub">Isi kolom kuning &rarr; tanda tangan</div>
    </div>
  </div>

  {{-- ═══════════════ HP UNITS TABLE ═══════════════ --}}
  <div class="section-title">
    <span>Inventaris Unit Handphone (HP)</span>
    <span class="section-count">{{ $units->count() }} unit total &nbsp;·&nbsp; {{ $readyCount }} ready</span>
  </div>
  <table>
    <thead>
      <tr>
        <th class="c" style="width:20px;background:#065F46">✓</th>
        <th style="width:20px">No</th>
        <th>Brand / Model</th>
        <th>Spesifikasi</th>
        <th>IMEI / SN</th>
        <th class="r">Harga Modal</th>
        <th class="c">Status Sistem</th>
        <th class="c" style="background:#1a4a2e">Qty Sistem</th>
        <th class="c" style="background:#7E22CE;min-width:60px">Qty Fisik</th>
        <th class="c" style="background:#7E22CE;min-width:50px">Selisih</th>
        <th style="min-width:100px;background:#7E22CE">Keterangan</th>
      </tr>
    </thead>
    <tbody>
      @php
        $groupedUnits = $units->groupBy(fn($u) => $u->model->brand->name ?? 'Lain-lain');
        $no = 1;
      @endphp
      @forelse($groupedUnits as $brandName => $brandUnits)
        <tr class="brand-row">
          <td colspan="11" style="text-align:left;padding:6px 8px;">
            ◆ BRAND: {{ strtoupper($brandName) }} ({{ $brandUnits->count() }} unit)
          </td>
        </tr>
        @foreach($brandUnits as $u)
          <tr>
            <td class="cb-cell"><span class="cb-box"></span></td>
            <td class="c muted">{{ $no++ }}</td>
            <td>
              <strong>{{ $u->model->name ?? '—' }}</strong>
              <br><span class="muted">{{ ucfirst($u->unit_type->value) }}{{ $u->grade ? ' · Grade '.$u->grade : '' }}</span>
            </td>
            <td class="muted">{{ $u->ram }}/{{ $u->rom }}<br>{{ $u->color }}</td>
            <td class="muted" style="font-size:6.5pt">
              {{ $u->imei ? 'IMEI: '.$u->imei : '' }}
              {{ $u->serial_number ? ($u->imei ? ' · ' : '').'SN: '.$u->serial_number : '' }}
              @if(!$u->imei && !$u->serial_number) — @endif
            </td>
            <td class="r">Rp {{ number_format((float)$u->purchase_price, 0, ',', '.') }}</td>
            <td class="c">
              @if($u->status->value === 'ready')
                <span class="badge badge-ready">Ready</span>
              @else
                <span class="badge badge-retur">Retur</span>
              @endif
            </td>
            <td class="c" style="font-weight:700">1</td>
            <td class="c fill-cell">&nbsp;</td>
            <td class="c fill-cell">&nbsp;</td>
            <td class="fill-cell-wide">&nbsp;</td>
          </tr>
        @endforeach
      @empty
        <tr><td colspan="11" style="text-align:center;padding:16px;color:#7A8AA8">Tidak ada data unit</td></tr>
      @endforelse
      <tr class="total-row">
        <td class="cb-cell">&nbsp;</td>
        <td colspan="6" style="text-align:right">TOTAL UNIT</td>
        <td class="c">{{ $units->count() }}</td>
        <td class="c fill-cell" style="background:#FEF3C7 !important">&nbsp;</td>
        <td class="c fill-cell" style="background:#FEF3C7 !important">&nbsp;</td>
        <td class="fill-cell-wide" style="background:#FEF3C7 !important">&nbsp;</td>
      </tr>
    </tbody>
  </table>

  {{-- ═══════════════ ACCESSORIES TABLE ═══════════════ --}}
  @if($accessories->count())
  <div class="section-title" style="margin-top:18px">
    <span>Inventaris Aksesoris</span>
    <span class="section-count">{{ $accessories->count() }} jenis &nbsp;·&nbsp; {{ number_format($accQty, 0, ',', '.') }} pcs total</span>
  </div>
  <table>
    <thead>
      <tr>
        <th class="c" style="width:20px;background:#065F46">✓</th>
        <th style="width:20px">No</th>
        <th>Nama Aksesoris</th>
        <th>Kategori</th>
        <th class="r">Harga Modal</th>
        <th class="r">Harga Jual</th>
        <th class="c">Status Stok</th>
        <th class="c" style="background:#1a4a2e">Qty Sistem</th>
        <th class="c" style="background:#7E22CE;min-width:60px">Qty Fisik</th>
        <th class="c" style="background:#7E22CE;min-width:50px">Selisih</th>
        <th style="min-width:100px;background:#7E22CE">Keterangan</th>
      </tr>
    </thead>
    <tbody>
      @php $ano = 1; $accTotalModal = 0; @endphp
      @foreach($accessories as $a)
      @php $aModal = (float)$a->purchase_price; $accTotalModal += $aModal * $a->stock_qty; @endphp
      <tr>
        <td class="cb-cell"><span class="cb-box"></span></td>
        <td class="c muted">{{ $ano++ }}</td>
        <td><strong>{{ $a->name }}</strong></td>
        <td class="muted">{{ $a->category ?: 'Lain-lain' }}</td>
        <td class="r">Rp {{ number_format($aModal, 0, ',', '.') }}</td>
        <td class="r">Rp {{ number_format((float)$a->selling_price, 0, ',', '.') }}</td>
        <td class="c">
          @if($a->stock_qty <= 0)
            <span class="badge badge-retur">Habis</span>
          @elseif($a->stock_qty <= 5)
            <span class="badge badge-warn">Menipis</span>
          @else
            <span class="badge badge-ready">Aman</span>
          @endif
        </td>
        <td class="c" style="font-weight:700">{{ $a->stock_qty }}</td>
        <td class="c fill-cell">&nbsp;</td>
        <td class="c fill-cell">&nbsp;</td>
        <td class="fill-cell-wide">&nbsp;</td>
      </tr>
      @endforeach
      <tr class="total-row">
        <td class="cb-cell">&nbsp;</td>
        <td colspan="6" style="text-align:right">TOTAL AKSESORIS</td>
        <td class="c">{{ number_format($accQty, 0, ',', '.') }} pcs</td>
        <td class="c fill-cell" style="background:#FEF3C7 !important">&nbsp;</td>
        <td class="c fill-cell" style="background:#FEF3C7 !important">&nbsp;</td>
        <td class="fill-cell-wide" style="background:#FEF3C7 !important">&nbsp;</td>
      </tr>
    </tbody>
  </table>
  @endif

  {{-- LEGEND --}}
  <div class="legend">
    <span><span class="legend-dot" style="background:#FFFBEB;border:1px solid #D1D5DB"></span>Kolom kuning = diisi secara fisik</span>
    <span><span class="badge badge-warn" style="font-size:7pt">Menipis</span> = stok &le; 5 pcs</span>
    <span><span class="badge badge-retur" style="font-size:7pt">Habis</span> = stok = 0</span>
  </div>

  {{-- SIGNATURE BLOCK --}}
  <div class="sig-section">
    <div class="sig-title">Verifikasi &amp; Persetujuan Stock Opname</div>
    <div class="sig-row">
      <div class="sig-box">
        <div class="sig-label">Diperiksa oleh (Petugas)</div>
        <div class="sig-name-line"></div>
        <div class="sig-role">Nama &amp; Tanda Tangan</div>
        <div class="check-note">Bertanggung jawab atas penghitungan fisik</div>
      </div>
      <div class="sig-box">
        <div class="sig-label">Diketahui oleh (Kasir/Staff)</div>
        <div class="sig-name-line"></div>
        <div class="sig-role">Nama &amp; Tanda Tangan</div>
        <div class="check-note">Menyaksikan proses penghitungan</div>
      </div>
      <div class="sig-box">
        <div class="sig-label">Disetujui oleh (Superadmin)</div>
        <div class="sig-name-line"></div>
        <div class="sig-role">Nama &amp; Tanda Tangan</div>
        <div class="check-note">Mengesahkan hasil stock opname</div>
      </div>
      <div class="sig-date-box">
        <div class="sig-label">Tanggal Pelaksanaan</div>
        <div class="sig-date-line"></div>
        <div class="sig-role">Tanggal / Bulan / Tahun</div>
        <div class="check-note" style="margin-top:8px">Tanda tangan &amp; cap toko</div>
        <div class="sig-date-line" style="margin-top:28px"></div>
      </div>
    </div>
  </div>

  {{-- FOOTER --}}
  <div class="footer">
    <span>Dokumen Stock Opname &mdash; Alex Phone Banjarnegara &mdash; {{ $printedAt }}</span>
    <span>Sistem POS Alex Phone &mdash; Dokumen ini sah apabila sudah ditandatangani</span>
  </div>

</div>

<script>window.onload = function(){ window.print(); }</script>
</body>
</html>
