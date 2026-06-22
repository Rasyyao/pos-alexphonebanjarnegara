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

  /* KPI CARDS TABLE LAYOUT FOR DOMPDF */
  .kpi-table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
  .kpi-table td.kpi-spacer { width: 1.5%; }
  .kpi-table td.kpi-card { border: 1px solid #E4E9F2; padding: 6px 8px; border-top: 2.5px solid #0A2540; background: #fff; vertical-align: top; width: {{ auth()->user()?->isSuperAdmin() ? '15.2%' : '18.8%' }}; }
  .kpi-label { font-size: 6.5pt; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #7A8AA8; margin-bottom: 2px; }
  .kpi-value { font-size: 10.5pt; font-weight: 700; color: #0A2540; white-space: nowrap; }
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
  <table class="kpi-table">
    <tr>
      <td class="kpi-card">
        <div class="kpi-label">Omzet</div>
        <div class="kpi-value">Rp {{ number_format($summary['revenue'], 0, ',', '.') }}</div>
      </td>
      <td class="kpi-spacer"></td>
      <td class="kpi-card">
        <div class="kpi-label">Cash Flow</div>
        <div class="kpi-value green">Rp {{ number_format($summary['saldoKas'] ?? 0, 0, ',', '.') }}</div>
        <div style="font-size: 6.5pt; color: #7A8AA8; margin-top: 3px; font-weight: bold; line-height: 1.2;">
          Saldo cash saat ini
        </div>
      </td>
      <td class="kpi-spacer"></td>
      <td class="kpi-card">
        <div class="kpi-label">Transfer</div>
        <div class="kpi-value" style="color: #4F46E5;">Rp {{ number_format($summary['saldoAtm'] ?? 0, 0, ',', '.') }}</div>
        <div style="font-size: 6.5pt; color: #7A8AA8; margin-top: 3px; font-weight: bold; line-height: 1.2;">
          Total transfer dalam periode
        </div>
      </td>
      <td class="kpi-spacer"></td>
      <td class="kpi-card">
        <div class="kpi-label">Pengeluaran</div>
        <div class="kpi-value red">Rp {{ number_format($summary['expenses'], 0, ',', '.') }}</div>
      </td>
      @if (auth()->user()?->isSuperAdmin())
      <td class="kpi-spacer"></td>
      <td class="kpi-card">
        <div class="kpi-label">Laba Bersih</div>
        <div class="kpi-value {{ $summary['net'] >= 0 ? 'green' : 'red' }}">Rp {{ number_format($summary['net'], 0, ',', '.') }}</div>
      </td>
      @endif
      <td class="kpi-spacer"></td>
      <td class="kpi-card">
        <div class="kpi-label">Hutang Aktif</div>
        <div class="kpi-value {{ ($summary['unpaidDebts'] ?? 0) > 0 ? 'red' : '' }}">Rp {{ number_format($summary['unpaidDebts'] ?? 0, 0, ',', '.') }}</div>
      </td>
    </tr>
  </table>

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
        @if (auth()->user()?->isSuperAdmin())
        <th class="right">Laba</th>
        @endif
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
          @if (auth()->user()?->isSuperAdmin())
          <td class="right {{ $s->profit > 0 ? 'green' : '' }}">Rp {{ number_format($s->profit, 0, ',', '.') }}</td>
          @endif
          <td class="center muted" style="font-size:7.5pt">
            {{ $s->payments->map(fn($p) => ucfirst($p->method->value))->join(', ') }}
          </td>
        </tr>
      @empty
        <tr><td colspan="{{ auth()->user()?->isSuperAdmin() ? 8 : 7 }}" style="text-align:center;padding:14px;color:#7A8AA8">Tidak ada transaksi pada periode ini</td></tr>
      @endforelse
      @if($sales->count())
      <tr class="total-row">
        <td colspan="5" style="text-align:right">TOTAL KESELURUHAN:</td>
        <td class="right">Rp {{ number_format($tTotal, 0, ',', '.') }}</td>
        @if (auth()->user()?->isSuperAdmin())
        <td class="right green">Rp {{ number_format($tLaba, 0, ',', '.') }}</td>
        @endif
        <td></td>
      </tr>
      @endif
    </tbody>
  </table>

  {{-- DEBT PAYMENTS TABLE --}}
  @if(isset($debtPayments) && $debtPayments->count() > 0)
  <div class="section-title" style="margin-top:16px">Rincian Pelunasan Hutang</div>
  <table>
    <thead>
      <tr>
        <th style="width:24px">No</th>
        <th>Invoice</th>
        <th class="center">Tgl. Penjualan</th>
        <th class="center">Tgl. Pelunasan</th>
        <th>Kasir</th>
        <th>Metode</th>
        <th class="right">Jumlah</th>
      </tr>
    </thead>
    <tbody>
      @php $no = 1; $tDebt = 0; @endphp
      @foreach($debtPayments as $p)
        @php
          $tDebt += (float)$p->amount;
          $method = $p->method instanceof \BackedEnum ? $p->method->value : $p->method;
        @endphp
        <tr>
          <td class="center muted">{{ $no++ }}</td>
          <td style="font-family:'Courier New',monospace;font-size:7.5pt">{{ $p->sale->invoice_number ?? '—' }}</td>
          <td class="center muted">{{ $p->sale->sale_date->format('d/m/Y') }}</td>
          <td class="center muted">{{ \Carbon\Carbon::parse($p->created_at)->format('d/m/Y') }}</td>
          <td class="muted">{{ $p->sale->creator->name ?? '—' }}</td>
          <td class="muted">{{ $method === 'cash' ? 'Tunai' : 'Transfer' }}</td>
          <td class="right green">Rp {{ number_format($p->amount, 0, ',', '.') }}</td>
        </tr>
      @endforeach
      <tr class="total-row">
        <td colspan="6" style="text-align:right">TOTAL PELUNASAN:</td>
        <td class="right green">Rp {{ number_format($tDebt, 0, ',', '.') }}</td>
      </tr>
    </tbody>
  </table>
  @endif

  {{-- HP STOCK PURCHASES TABLE --}}
  @if(isset($hpPurchases) && $hpPurchases->count() > 0)
  <div class="section-title" style="margin-top:16px">Pembelian Stok HP</div>
  <table>
    <thead>
      <tr>
        <th style="width:24px">No</th>
        <th class="center">Tanggal</th>
        <th>Nama HP</th>
        <th>IMEI / SN</th>
        <th>Kondisi</th>
        <th>Metode</th>
        <th>Inputter</th>
        <th class="right">Harga Beli</th>
      </tr>
    </thead>
    <tbody>
      @php $no = 1; $tHP = 0; @endphp
      @foreach($hpPurchases as $u)
        @php
          $tHP += (float)$u->purchase_price;
          $brand = $u->model->brand->name ?? '';
          $model = $u->model->name ?? '';
          $spec  = trim("{$brand} {$model} ({$u->ram}/{$u->rom}) - {$u->color}");
          $imeiSn = collect([$u->imei ? 'IMEI: '.$u->imei : null, $u->serial_number ? 'SN: '.$u->serial_number : null])->filter()->join(' | ');
          $kondisi = ucfirst($u->unit_type->value ?? '') . ($u->grade ? ' Grade '.$u->grade : '');
          $method = $u->purchase_payment_method ?? 'cash';
          $methodLabel = $method === 'transfer' ? 'Transfer' : 'Tunai';
        @endphp
        <tr>
          <td class="center muted">{{ $no++ }}</td>
          <td class="center muted">{{ \Carbon\Carbon::parse($u->purchase_date)->format('d/m/Y') }}</td>
          <td>{{ $spec }}</td>
          <td class="muted" style="font-size:7pt">{{ $imeiSn ?: '—' }}</td>
          <td class="muted">{{ $kondisi }}</td>
          <td class="muted">{{ $methodLabel }}</td>
          <td class="muted">{{ $u->creator->name ?? '—' }}</td>
          <td class="right red">Rp {{ number_format($u->purchase_price, 0, ',', '.') }}</td>
        </tr>
      @endforeach
      <tr class="total-row">
        <td colspan="7" style="text-align:right">TOTAL PEMBELIAN STOK HP:</td>
        <td class="right red">Rp {{ number_format($tHP, 0, ',', '.') }}</td>
      </tr>
    </tbody>
  </table>
  @endif

  {{-- OPERATIONAL EXPENSES TABLE --}}
  @if(isset($operationalExpenses) && $operationalExpenses->count() > 0)
  <div class="section-title" style="margin-top:16px">Rincian Pengeluaran Operasional</div>
  <table>
    <thead>
      <tr>
        <th style="width:24px">No</th>
        <th class="center">Tanggal</th>
        <th>Keterangan</th>
        <th class="center">Kategori</th>
        <th class="center">Metode</th>
        <th>Catatan</th>
        <th>Dicatat Oleh</th>
        <th class="right">Jumlah</th>
      </tr>
    </thead>
    <tbody>
      @php $no = 1; $tExp = 0; @endphp
      @foreach($operationalExpenses as $e)
        @php
          $tExp += (float)$e->amount;
          $catLabel = match($e->category) {
            'tarik_owner' => 'Tarik Saldo Owner',
            'listrik'     => 'Listrik & Gas',
            'stok_hp'     => 'Stok HP',
            default       => ucwords($e->category)
          };
          $methodLabel = match($e->payment_method ?? 'cash') {
            'transfer' => 'Transfer',
            'cash'     => 'Tunai',
            default    => ucfirst($e->payment_method ?? 'cash')
          };
        @endphp
        <tr>
          <td class="center muted">{{ $no++ }}</td>
          <td class="center muted">{{ \Carbon\Carbon::parse($e->expense_date)->format('d/m/Y') }}</td>
          <td>{{ $e->description }}</td>
          <td class="center muted">{{ $catLabel }}</td>
          <td class="center muted">{{ $methodLabel }}</td>
          <td class="muted" style="font-size:7pt">{{ $e->notes ?: '—' }}</td>
          <td class="muted">{{ $e->creator->name ?? '—' }}</td>
          <td class="right red">Rp {{ number_format($e->amount, 0, ',', '.') }}</td>
        </tr>
      @endforeach
      <tr class="total-row">
        <td colspan="7" style="text-align:right">TOTAL PENGELUARAN OPERASIONAL:</td>
        <td class="right red">Rp {{ number_format($tExp, 0, ',', '.') }}</td>
      </tr>
    </tbody>
  </table>
  @endif

  <div class="footer">Dokumen dicetak otomatis oleh sistem POS Alex Phone &mdash; {{ $printedAt }}</div>
</div>
</body>
</html>
