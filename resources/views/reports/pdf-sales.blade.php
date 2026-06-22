<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8" />
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: Arial, Helvetica, sans-serif; font-size: 9pt; color: #0A2540; background: #fff; }
  .page { padding: 22px 28px 18px; }

  /* HEADER */
  .header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2.5px solid #0A2540; padding-bottom: 10px; margin-bottom: 14px; }
  .store-name { font-size: 15pt; font-weight: 700; color: #0A2540; }
  .store-sub  { font-size: 8pt; color: #3D5374; margin-top: 2px; }
  .doc-label  { font-size: 11pt; font-weight: 700; color: #0A2540; text-transform: uppercase; text-align: right; }
  .doc-meta   { font-size: 7.5pt; color: #3D5374; margin-top: 3px; text-align: right; }

  /* PERIOD BAR */
  .period-bar { border-left: 3px solid #0A2540; padding: 6px 10px; margin-bottom: 12px; background: #F4F6FB; }
  .period-label { font-size: 7.5pt; font-weight: 700; text-transform: uppercase; letter-spacing: 0.7px; color: #7A8AA8; }
  .period-value { font-size: 11pt; font-weight: 700; color: #0A2540; margin-top: 2px; }

  /* KPI */
  .kpi-row { display: flex; gap: 8px; margin-bottom: 14px; }
  .kpi { flex: 1; border: 1px solid #E4E9F2; padding: 9px 11px; border-top: 3px solid #0A2540; }
  .kpi-label { font-size: 7pt; font-weight: 700; text-transform: uppercase; letter-spacing: 0.7px; color: #7A8AA8; margin-bottom: 3px; }
  .kpi-value { font-size: 11.5pt; font-weight: 700; color: #0A2540; }
  .kpi-value.green { color: #065F46; }

  /* SECTION TITLE */
  .section-title { font-size: 8pt; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; color: #0A2540; border-left: 3px solid #0A2540; padding-left: 6px; margin-bottom: 8px; }

  /* TABLE */
  table { width: 100%; border-collapse: collapse; font-size: 8pt; }
  thead tr { background: #0A2540; color: #fff; }
  thead th { padding: 6px 8px; text-align: left; font-weight: 700; font-size: 7.5pt; text-transform: uppercase; letter-spacing: 0.4px; white-space: nowrap; }
  thead th.right  { text-align: right; }
  thead th.center { text-align: center; }
  tbody tr { border-bottom: 1px solid #E4E9F2; }
  tbody tr:nth-child(even) { background: #F4F6FB; }
  tbody td { padding: 5px 8px; color: #0A2540; vertical-align: top; }
  tbody td.right  { text-align: right; }
  tbody td.center { text-align: center; }
  tbody td.muted  { color: #7A8AA8; font-size: 7.5pt; }
  .green { color: #065F46; }
  .total-row td { background: #E8EDF5 !important; font-weight: 700; border-top: 2px solid #0A2540; }

  /* FOOTER */
  .footer { margin-top: 16px; border-top: 1px solid #E4E9F2; padding-top: 8px; text-align: right; font-size: 7.5pt; color: #7A8AA8; font-style: italic; }
</style>
</head>
<body>
<div class="page">

  {{-- HEADER --}}
  <div class="header">
    <div>
      <div class="store-name">ALEX PHONE BANJARNEGARA</div>
      <div class="store-sub">Pusat Penjualan &amp; Servis Smartphone — Banjarnegara, Jawa Tengah</div>
    </div>
    <div>
      <div class="doc-label">Laporan Penjualan Harian</div>
      <div class="doc-meta">Dicetak: {{ $printedAt }}</div>
    </div>
  </div>

  {{-- PERIOD --}}
  <div class="period-bar">
    <div class="period-label">Tanggal Laporan</div>
    <div class="period-value">{{ $date }}</div>
  </div>

  {{-- KPI --}}
  <div class="kpi-row">
    <div class="kpi">
      <div class="kpi-label">Total Transaksi</div>
      <div class="kpi-value">{{ $txCount }} transaksi</div>
    </div>
    <div class="kpi">
      <div class="kpi-label">Total Omzet</div>
      <div class="kpi-value">Rp {{ number_format($totalRev, 0, ',', '.') }}</div>
    </div>
    <div class="kpi">
      <div class="kpi-label">Laba Bersih</div>
      <div class="kpi-value green">Rp {{ number_format($totalProfit, 0, ',', '.') }}</div>
    </div>
    <div class="kpi">
      <div class="kpi-label">Cash / Transfer / Utang</div>
      <div class="kpi-value" style="font-size:9pt">
        {{ number_format($totalCash,0,',','.') }} /
        {{ number_format($totalTransfer,0,',','.') }} /
        {{ number_format($totalDebt,0,',','.') }}
      </div>
    </div>
    <div class="kpi">
      <div class="kpi-label">Pengeluaran</div>
      <div class="kpi-value" style="color:#B91C1C">Rp {{ number_format($operationalExpenseTotal, 0, ',', '.') }}</div>
    </div>
  </div>

  {{-- TABLE --}}
  <div class="section-title">Rincian Transaksi</div>
  <table>
    <thead>
      <tr>
        <th style="width:24px">No</th>
        <th>Invoice</th>
        <th>Item Terjual</th>
        <th>Kasir</th>
        <th class="right">Total Jual</th>
        <th class="right">Laba</th>
        <th class="center">Cash</th>
        <th class="center">Transfer</th>
        <th class="center">Utang</th>
      </tr>
    </thead>
    <tbody>
      @php $no = 1; @endphp
      @forelse($sales as $s)
        @php
          $sCash = $sTransfer = $sDebt = 0;
          foreach ($s->payments->where('source', 'sale') as $p) {
              $m = $p->method->value ?? $p->method;
              if ($m === 'cash')         $sCash     += $p->amount;
              elseif ($m === 'transfer') $sTransfer += $p->amount;
              elseif ($m === 'utang')    $sDebt     += $p->amount;
          }
          $items = $s->items->map(fn($i) => $i->unit_id
              ? (($i->unit->model->brand->name ?? '') . ' ' . ($i->unit->model->name ?? ''))
              : (($i->accessory->name ?? '—') . ' ×' . $i->quantity)
          )->join(', ');
        @endphp
        <tr>
          <td class="center muted">{{ $no++ }}</td>
          <td style="font-family:'Courier New',monospace;font-size:7.5pt">{{ $s->invoice_number }}</td>
          <td>{{ $items }}</td>
          <td class="muted">{{ $s->creator->name ?? '—' }}</td>
          <td class="right">Rp {{ number_format($s->total_price, 0, ',', '.') }}</td>
          <td class="right green">Rp {{ number_format($s->profit, 0, ',', '.') }}</td>
          <td class="center muted">{{ $sCash > 0 ? 'Rp '.number_format($sCash,0,',','.') : '—' }}</td>
          <td class="center muted">{{ $sTransfer > 0 ? 'Rp '.number_format($sTransfer,0,',','.') : '—' }}</td>
          <td class="center muted">{{ $sDebt > 0 ? 'Rp '.number_format($sDebt,0,',','.') : '—' }}</td>
        </tr>
      @empty
        <tr>
          <td colspan="9" style="text-align:center;padding:16px;color:#7A8AA8">Tidak ada transaksi pada tanggal ini</td>
        </tr>
      @endforelse
      @if(count($sales) > 0)
      <tr class="total-row">
        <td colspan="4" style="text-align:right">TOTAL:</td>
        <td class="right">Rp {{ number_format($totalRev, 0, ',', '.') }}</td>
        <td class="right green">Rp {{ number_format($totalProfit, 0, ',', '.') }}</td>
        <td class="center">{{ $totalCash > 0 ? 'Rp '.number_format($totalCash,0,',','.') : '—' }}</td>
        <td class="center">{{ $totalTransfer > 0 ? 'Rp '.number_format($totalTransfer,0,',','.') : '—' }}</td>
        <td class="center">{{ $totalDebt > 0 ? 'Rp '.number_format($totalDebt,0,',','.') : '—' }}</td>
      </tr>
      @endif
    </tbody>
  </table>

  <div class="section-title" style="margin-top:14px">Rekap Pengeluaran Operasional</div>
  <table>
    <thead>
      <tr>
        <th style="width:24px">No</th>
        <th>Keterangan</th>
        <th>Kategori</th>
        <th class="center">Metode</th>
        <th class="right">Jumlah</th>
      </tr>
    </thead>
    <tbody>
      @forelse($operationalExpenses as $i => $expense)
        <tr>
          <td class="center muted">{{ $i + 1 }}</td>
          <td>{{ $expense->description }}</td>
          <td class="muted">{{ $expense->category === 'tarik_owner' ? 'Tarik Saldo Owner' : ($expense->category === 'listrik' ? 'Listrik & Gas' : ucwords($expense->category)) }}</td>
          <td class="center muted">{{ ($expense->payment_method ?? 'cash') === 'transfer' ? 'Transfer' : 'Tunai' }}</td>
          <td class="right">Rp {{ number_format($expense->amount, 0, ',', '.') }}</td>
        </tr>
      @empty
        <tr>
          <td colspan="5" style="text-align:center;padding:12px;color:#7A8AA8">Tidak ada pengeluaran operasional pada tanggal ini</td>
        </tr>
      @endforelse
      @if($operationalExpenses->count() > 0)
      <tr class="total-row">
        <td colspan="4" style="text-align:right">TOTAL PENGELUARAN:</td>
        <td class="right">Rp {{ number_format($operationalExpenseTotal, 0, ',', '.') }}</td>
      </tr>
      @endif
    </tbody>
  </table>

  <div class="footer">Dokumen dicetak otomatis oleh sistem POS Alex Phone &mdash; {{ $printedAt }}</div>
</div>
</body>
</html>
