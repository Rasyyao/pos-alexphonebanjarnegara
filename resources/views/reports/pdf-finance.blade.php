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

  .page { padding: 22px 28px 18px; }

  /* HEADER */
  .header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2.5px solid #0A2540; padding-bottom: 10px; margin-bottom: 14px; }
  .header-left .store-name { font-size: 15pt; font-weight: 700; letter-spacing: -0.3px; color: #0A2540; }
  .header-left .store-sub  { font-size: 8pt; color: #3D5374; margin-top: 2px; }
  .doc-label { font-size: 11pt; font-weight: 700; color: #0A2540; letter-spacing: 0.5px; text-transform: uppercase; text-align: right; }
  .doc-meta  { font-size: 7.5pt; color: #3D5374; margin-top: 3px; text-align: right; }

  /* PERIOD BAR */
  .period-bar { border-left: 3px solid #0A2540; padding: 6px 10px; margin-bottom: 12px; background: #F4F6FB; }
  .period-label { font-size: 7.5pt; font-weight: 700; text-transform: uppercase; letter-spacing: 0.7px; color: #7A8AA8; }
  .period-value { font-size: 11pt; font-weight: 700; color: #0A2540; margin-top: 2px; }

  /* KPI CARDS */
  .kpi-row { display: flex; gap: 8px; margin-bottom: 14px; }
  .kpi { flex: 1; border: 1px solid #E4E9F2; padding: 9px 11px; border-top: 3px solid #0A2540; }
  .kpi-label { font-size: 7pt; font-weight: 700; text-transform: uppercase; letter-spacing: 0.7px; color: #7A8AA8; margin-bottom: 3px; }
  .kpi-value { font-size: 12pt; font-weight: 700; color: #0A2540; }
  .kpi-value.green { color: #065F46; }
  .kpi-value.red   { color: #B91C1C; }

  /* SECTION TITLE */
  .section-title { font-size: 8pt; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; color: #0A2540; border-left: 3px solid #0A2540; padding-left: 6px; margin-bottom: 8px; margin-top: 14px; }

  /* TABLE */
  table { width: 100%; border-collapse: collapse; font-size: 8pt; }
  thead tr { background: #0A2540; color: #fff; }
  thead th { padding: 6px 8px; text-align: left; font-weight: 700; font-size: 7.5pt; text-transform: uppercase; letter-spacing: 0.4px; white-space: nowrap; }
  thead th.right { text-align: right; }
  thead th.center { text-align: center; }
  tbody tr { border-bottom: 1px solid #E4E9F2; }
  tbody tr:nth-child(even) { background: #F4F6FB; }
  tbody td { padding: 5px 8px; color: #0A2540; }
  tbody td.right { text-align: right; }
  tbody td.center { text-align: center; }
  tbody td.muted { color: #7A8AA8; font-size: 7.5pt; }
  .total-row td { background: #E8EDF5 !important; font-weight: 700; border-top: 2px solid #0A2540; }
  .green { color: #065F46; }
  .red   { color: #B91C1C; }

  /* FOOTER */
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
    <div>
      <div class="doc-label">Laporan Keuangan</div>
      <div class="doc-meta">Dicetak: {{ $printedAt }}</div>
    </div>
  </div>

  {{-- PERIOD --}}
  <div class="period-bar">
    <div class="period-label">Periode Laporan</div>
    <div class="period-value">{{ $periodStr }}</div>
  </div>

  {{-- KPI --}}
  <div class="kpi-row">
    <div class="kpi">
      <div class="kpi-label">Total Omzet</div>
      <div class="kpi-value">Rp {{ number_format($summary['revenue'], 0, ',', '.') }}</div>
    </div>
    <div class="kpi">
      <div class="kpi-label">Laba Kotor</div>
      <div class="kpi-value {{ $summary['profit'] >= 0 ? 'green' : 'red' }}">Rp {{ number_format($summary['profit'], 0, ',', '.') }}</div>
    </div>
    <div class="kpi">
      <div class="kpi-label">Pengeluaran</div>
      <div class="kpi-value">Rp {{ number_format($summary['expenses'], 0, ',', '.') }}</div>
    </div>
    <div class="kpi">
      <div class="kpi-label">Laba Bersih</div>
      <div class="kpi-value {{ $summary['net'] >= 0 ? 'green' : 'red' }}">Rp {{ number_format($summary['net'], 0, ',', '.') }}</div>
    </div>
  </div>

  {{-- SALES TABLE --}}
  <div class="section-title">Rincian Transaksi Penjualan</div>
  <table>
    <thead>
      <tr>
        <th style="width:24px">No</th>
        <th>Invoice</th>
        <th class="center">Tanggal</th>
        <th>Item</th>
        <th>Kasir</th>
        <th class="right">Total Jual</th>
        <th class="right">Laba</th>
        <th class="center">Bayar</th>
      </tr>
    </thead>
    <tbody>
      @php $no = 1; $tTotal = 0; $tLaba = 0; @endphp
      @forelse($sales as $s)
        @php
          $tTotal += (float)$s->total_price;
          $tLaba  += (float)$s->profit;
          $items  = $s->items->map(fn($i) => $i->unit_id
            ? (($i->unit->model->brand->name ?? '').' '.($i->unit->model->name ?? ''))
            : (($i->accessory->name ?? '—').' ×'.$i->quantity)
          )->join(', ');
        @endphp
        <tr>
          <td class="center muted">{{ $no++ }}</td>
          <td style="font-family:'Courier New',monospace;font-size:7.5pt">{{ $s->invoice_number }}</td>
          <td class="center muted">{{ $s->sale_date->format('d/m/Y') }}</td>
          <td>{{ $items }}</td>
          <td class="muted">{{ $s->creator->name ?? '—' }}</td>
          <td class="right">Rp {{ number_format($s->total_price, 0, ',', '.') }}</td>
          <td class="right {{ $s->profit > 0 ? 'green' : '' }}">Rp {{ number_format($s->profit, 0, ',', '.') }}</td>
          <td class="center muted" style="font-size:7.5pt">
            {{ $s->payments->map(fn($p) => ucfirst($p->method->value))->join(', ') }}
          </td>
        </tr>
      @empty
        <tr><td colspan="8" style="text-align:center;padding:14px;color:#7A8AA8">Tidak ada transaksi pada periode ini</td></tr>
      @endforelse
      @if($sales->count())
      <tr class="total-row">
        <td colspan="5" style="text-align:right">TOTAL KESELURUHAN:</td>
        <td class="right">Rp {{ number_format($tTotal, 0, ',', '.') }}</td>
        <td class="right green">Rp {{ number_format($tLaba, 0, ',', '.') }}</td>
        <td></td>
      </tr>
      @endif
    </tbody>
  </table>

  <div class="footer">Dokumen dicetak otomatis oleh sistem POS Alex Phone &mdash; {{ $printedAt }}</div>
</div>
</body>
</html>
