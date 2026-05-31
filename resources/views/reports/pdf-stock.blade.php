<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8" />
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 9pt;
    color: #0A2540;
    background: #fff;
  }

  /* ── LAYOUT ── */
  .page { padding: 22px 28px 18px; }

  /* ── HEADER ── */
  .header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2.5px solid #0A2540; padding-bottom: 10px; margin-bottom: 14px; }
  .header-left .store-name { font-size: 15pt; font-weight: 700; letter-spacing: -0.3px; color: #0A2540; }
  .header-left .store-sub  { font-size: 8pt; color: #3D5374; margin-top: 2px; }
  .header-right { text-align: right; }
  .doc-label { font-size: 11pt; font-weight: 700; color: #0A2540; letter-spacing: 0.5px; text-transform: uppercase; }
  .doc-meta  { font-size: 7.5pt; color: #3D5374; margin-top: 3px; }

  /* ── KPI CARDS ── */
  .kpi-row { display: flex; gap: 10px; margin-bottom: 14px; }
  .kpi { flex: 1; border: 1px solid #E4E9F2; padding: 10px 12px; border-top: 3px solid #0A2540; }
  .kpi-label { font-size: 7pt; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; color: #7A8AA8; margin-bottom: 4px; }
  .kpi-value { font-size: 13pt; font-weight: 700; color: #0A2540; }
  .kpi-sub   { font-size: 7.5pt; color: #3D5374; margin-top: 2px; }

  /* ── SECTION TITLE ── */
  .section-title { font-size: 8pt; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; color: #0A2540; border-left: 3px solid #0A2540; padding-left: 6px; margin-bottom: 8px; margin-top: 14px; }

  /* ── TABLE ── */
  table { width: 100%; border-collapse: collapse; font-size: 8pt; }
  thead tr { background: #0A2540; color: #fff; }
  thead th { padding: 6px 8px; text-align: left; font-weight: 700; font-size: 7.5pt; text-transform: uppercase; letter-spacing: 0.5px; white-space: nowrap; }
  thead th.right { text-align: right; }
  thead th.center { text-align: center; }
  tbody tr { border-bottom: 1px solid #E4E9F2; }
  tbody tr:nth-child(even) { background: #F4F6FB; }
  tbody td { padding: 5px 8px; color: #0A2540; vertical-align: top; }
  tbody td.right { text-align: right; font-family: 'Courier New', monospace; }
  tbody td.center { text-align: center; }
  tbody td.muted { color: #7A8AA8; font-size: 7.5pt; }

  /* status badges */
  .badge { display: inline-block; padding: 1px 6px; border-radius: 2px; font-size: 7pt; font-weight: 700; }
  .badge-ready  { background: #ECFDF5; color: #065F46; }
  .badge-sold   { background: #F1F5F9; color: #475569; }
  .badge-retur  { background: #FEF2F2; color: #B91C1C; }

  /* total row */
  .total-row td { background: #E8EDF5; font-weight: 700; border-top: 2px solid #0A2540; }

  /* ── FOOTER ── */
  .footer { margin-top: 16px; border-top: 1px solid #E4E9F2; padding-top: 8px; text-align: right; font-size: 7.5pt; color: #7A8AA8; font-style: italic; }
</style>
</head>
<body>
<div class="page">

  {{-- HEADER --}}
  <div class="header">
    <div class="header-left">
      <div class="store-name">ALEX PHONE BANJARNEGARA</div>
      <div class="store-sub">Pusat Penjualan &amp; Servis Smartphone — Banjarnegara, Jawa Tengah</div>
    </div>
    <div class="header-right">
      <div class="doc-label">Laporan Stock Opname</div>
      <div class="doc-meta">Dicetak: {{ $printedAt }}</div>
    </div>
  </div>

  {{-- KPI CARDS --}}
  <div class="kpi-row">
    <div class="kpi">
      <div class="kpi-label">Total Unit HP</div>
      <div class="kpi-value">{{ number_format($units->count(), 0, ',', '.') }} unit</div>
      <div class="kpi-sub">Ready: {{ $readyCount }} &nbsp;|&nbsp; Terjual: {{ $soldCount }}</div>
    </div>
    <div class="kpi">
      <div class="kpi-label">Nilai Modal Stok Ready</div>
      <div class="kpi-value">Rp {{ number_format($assetModal, 0, ',', '.') }}</div>
      <div class="kpi-sub">Est. jual: Rp {{ number_format($assetJual, 0, ',', '.') }}</div>
    </div>
    <div class="kpi">
      <div class="kpi-label">Total Aksesoris</div>
      <div class="kpi-value">{{ number_format($accessories->count(), 0, ',', '.') }} jenis</div>
      <div class="kpi-sub">Total qty: {{ number_format($accQty, 0, ',', '.') }} pcs &nbsp;|&nbsp; Modal: Rp {{ number_format($accModal, 0, ',', '.') }}</div>
    </div>
    <div class="kpi">
      <div class="kpi-label">Est. Laba Potensial (HP)</div>
      @php $estLaba = $assetJual - $assetModal; @endphp
      <div class="kpi-value">Rp {{ number_format($estLaba, 0, ',', '.') }}</div>
      <div class="kpi-sub">{{ $assetJual > 0 ? round(($estLaba / $assetJual) * 100) : 0 }}% margin dari harga jual</div>
    </div>
  </div>

  {{-- HP TABLE --}}
  <div class="section-title">Inventaris Unit Handphone</div>
  <table>
    <thead>
      <tr>
        <th style="width:24px">No</th>
        <th>Brand / Model</th>
        <th>Spesifikasi</th>
        <th>IMEI / SN</th>
        <th class="right">Harga Modal</th>
        <th class="right">Harga Jual</th>
        <th class="right">Est. Laba</th>
        <th class="center">Status</th>
        <th class="center">Tgl. Beli</th>
      </tr>
    </thead>
    <tbody>
      @php
        $no = 1;
        $totalModal = 0; $totalJual = 0; $totalLaba = 0;
      @endphp
      @forelse($units as $u)
        @php
          $modal = (float)$u->purchase_price;
          $jual  = (float)($u->selling_price ?? 0);
          $laba  = $jual - $modal;
          $totalModal += $modal;
          if ($jual > 0) { $totalJual += $jual; $totalLaba += $laba; }
          $status = $u->status->value;
        @endphp
        <tr>
          <td class="center muted">{{ $no++ }}</td>
          <td>
            <strong>{{ $u->model->brand->name ?? '—' }} {{ $u->model->name ?? '' }}</strong>
            <br><span class="muted">{{ ucfirst($u->unit_type->value) }}{{ $u->grade ? ' · Grade '.$u->grade : '' }}</span>
          </td>
          <td class="muted">{{ $u->ram }}/{{ $u->rom }} &nbsp; {{ $u->color }}</td>
          <td class="muted" style="font-size:7pt">
            {{ $u->imei ?: ($u->serial_number ?: '—') }}
          </td>
          <td class="right">Rp {{ number_format($modal, 0, ',', '.') }}</td>
          <td class="right">{{ $jual > 0 ? 'Rp '.number_format($jual,0,',','.') : '—' }}</td>
          <td class="right">{{ $jual > 0 ? 'Rp '.number_format($laba,0,',','.') : '—' }}</td>
          <td class="center">
            @if($status === 'ready')
              <span class="badge badge-ready">Ready</span>
            @elseif($status === 'sold')
              <span class="badge badge-sold">Terjual</span>
            @else
              <span class="badge badge-retur">{{ ucfirst($status) }}</span>
            @endif
          </td>
          <td class="center muted">{{ $u->purchase_date ? $u->purchase_date->format('d/m/Y') : '—' }}</td>
        </tr>
      @empty
        <tr><td colspan="9" style="text-align:center;padding:16px;color:#7A8AA8">Tidak ada data unit</td></tr>
      @endforelse
      <tr class="total-row">
        <td colspan="4" style="text-align:right">TOTAL</td>
        <td class="right">Rp {{ number_format($totalModal, 0, ',', '.') }}</td>
        <td class="right">Rp {{ number_format($totalJual, 0, ',', '.') }}</td>
        <td class="right">Rp {{ number_format($totalLaba, 0, ',', '.') }}</td>
        <td colspan="2"></td>
      </tr>
    </tbody>
  </table>

  {{-- ACCESSORIES TABLE --}}
  @if($accessories->count())
  <div class="section-title">Inventaris Aksesoris</div>
  <table>
    <thead>
      <tr>
        <th style="width:24px">No</th>
        <th>Nama Aksesoris</th>
        <th>Kategori</th>
        <th class="center">Stok</th>
        <th class="right">Harga Modal</th>
        <th class="right">Harga Jual</th>
        <th class="right">Laba/Pcs</th>
        <th class="right">Total Modal</th>
        <th class="right">Total Jual</th>
      </tr>
    </thead>
    <tbody>
      @php $ano = 1; $aTotalModal = 0; $aTotalJual = 0; @endphp
      @foreach($accessories as $a)
        @php
          $aModal = (float)$a->purchase_price;
          $aJual  = (float)$a->selling_price;
          $aTM = $aModal * $a->stock_qty;
          $aTJ = $aJual * $a->stock_qty;
          $aTotalModal += $aTM; $aTotalJual += $aTJ;
        @endphp
        <tr>
          <td class="center muted">{{ $ano++ }}</td>
          <td><strong>{{ $a->name }}</strong></td>
          <td class="muted">{{ $a->category ?: 'Lain-lain' }}</td>
          <td class="center {{ $a->stock_qty <= 5 ? 'text-red' : '' }}">
            {{ $a->stock_qty }}
            @if($a->stock_qty <= 5) <span style="color:#B91C1C;font-size:7pt">(!)</span> @endif
          </td>
          <td class="right">Rp {{ number_format($aModal, 0, ',', '.') }}</td>
          <td class="right">Rp {{ number_format($aJual,  0, ',', '.') }}</td>
          <td class="right">Rp {{ number_format($aJual - $aModal, 0, ',', '.') }}</td>
          <td class="right">Rp {{ number_format($aTM, 0, ',', '.') }}</td>
          <td class="right">Rp {{ number_format($aTJ, 0, ',', '.') }}</td>
        </tr>
      @endforeach
      <tr class="total-row">
        <td colspan="3" style="text-align:right">TOTAL</td>
        <td class="center">{{ number_format($accQty, 0, ',', '.') }} pcs</td>
        <td colspan="3"></td>
        <td class="right">Rp {{ number_format($aTotalModal, 0, ',', '.') }}</td>
        <td class="right">Rp {{ number_format($aTotalJual, 0, ',', '.') }}</td>
      </tr>
    </tbody>
  </table>
  @endif

  <div class="footer">Dokumen dicetak otomatis oleh sistem POS Alex Phone &mdash; {{ $printedAt }}</div>

</div>
</body>
</html>
