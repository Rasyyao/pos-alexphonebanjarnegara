<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8" />
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: Arial, Helvetica, sans-serif; font-size: 8.5pt; color: #111827; background: #fff; }
  .page { padding: 18px 24px 16px; }

  /* HEADER */
  .header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #111827; padding-bottom: 8px; margin-bottom: 12px; }
  .store-name { font-size: 14pt; font-weight: 700; color: #111827; }
  .store-sub  { font-size: 7.5pt; color: #4B5563; margin-top: 2px; }
  .doc-label  { font-size: 10pt; font-weight: 700; color: #111827; text-align: right; text-transform: uppercase; letter-spacing: 0.5px; }
  .doc-meta   { font-size: 7pt; color: #6B7280; margin-top: 3px; text-align: right; }

  /* KPI */
  .kpi-row { display: flex; gap: 8px; margin-bottom: 12px; }
  .kpi { flex: 1; border: 1px solid #D1D5DB; border-top: 2.5px solid #374151; padding: 8px 10px; }
  .kpi-label { font-size: 7pt; font-weight: 700; text-transform: uppercase; letter-spacing: 0.6px; color: #6B7280; margin-bottom: 3px; }
  .kpi-value { font-size: 11pt; font-weight: 700; color: #111827; }
  .kpi-sub   { font-size: 7pt; color: #6B7280; margin-top: 2px; }

  /* SECTION */
  .section-title { font-size: 8pt; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; color: #111827; border-left: 3px solid #374151; padding-left: 6px; margin: 12px 0 5px; }

  /* TABLE */
  table { width: 100%; border-collapse: collapse; font-size: 8pt; }
  thead th { padding: 5px 7px; text-align: left; font-weight: 700; font-size: 7pt; text-transform: uppercase; letter-spacing: 0.4px; color: #111827; background: #F3F4F6; border-top: 1px solid #9CA3AF; border-bottom: 1.5px solid #374151; white-space: nowrap; }
  thead th.r { text-align: right; }
  thead th.c { text-align: center; }
  tbody tr { border-bottom: 1px solid #E5E7EB; }
  tbody tr:nth-child(even) { background: #F9FAFB; }
  tbody td { padding: 4px 7px; color: #111827; vertical-align: top; }
  tbody td.r { text-align: right; font-family: 'Courier New', monospace; }
  tbody td.c { text-align: center; }
  tbody td.muted { color: #6B7280; font-size: 7.5pt; }

  /* checkbox */
  .cb-cell { text-align: center; width: 16px; padding: 4px 3px !important; }
  .cb-box { display: inline-block; width: 11px; height: 11px; border: 1.5px solid #374151; border-radius: 1px; vertical-align: middle; background: #fff; }

  /* badges */
  .badge { display: inline-block; padding: 1px 5px; border-radius: 2px; font-size: 7pt; font-weight: 700; border: 1px solid; }
  .badge-ready  { border-color: #A7F3D0; color: #065F46; }
  .badge-sold   { border-color: #D1D5DB; color: #374151; }
  .badge-retur  { border-color: #FECACA; color: #B91C1C; }

  /* total row */
  .total-row td { font-weight: 700; border-top: 1.5px solid #374151; background: #F3F4F6 !important; }

  /* FOOTER */
  .footer { margin-top: 14px; border-top: 1px solid #E5E7EB; padding-top: 6px; text-align: right; font-size: 7pt; color: #9CA3AF; font-style: italic; }

  @media print {
    @page { size: A4 landscape; margin: 10mm; }
    body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
  }
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
      <div class="doc-label">Laporan Stock Opname</div>
      <div class="doc-meta">Dicetak: {{ $printedAt }}</div>
    </div>
  </div>

  {{-- KPI --}}
  <div class="kpi-row">
    <div class="kpi">
      <div class="kpi-label">Total Unit HP</div>
      <div class="kpi-value">{{ $units->count() }} unit</div>
      <div class="kpi-sub">Ready: {{ $readyCount }} &nbsp;|&nbsp; Terjual: {{ $soldCount }}</div>
    </div>
    <div class="kpi">
      <div class="kpi-label">Nilai Modal Stok Ready</div>
      <div class="kpi-value">Rp {{ number_format($assetModal, 0, ',', '.') }}</div>
      <div class="kpi-sub">Berdasarkan harga beli unit ready</div>
    </div>
    <div class="kpi">
      <div class="kpi-label">Total Aksesoris</div>
      <div class="kpi-value">{{ $accessories->count() }} jenis</div>
      <div class="kpi-sub">Qty: {{ number_format($accQty, 0, ',', '.') }} pcs &nbsp;|&nbsp; Modal: Rp {{ number_format($accModal, 0, ',', '.') }}</div>
    </div>
  </div>

  {{-- HP TABLE --}}
  <div class="section-title">Inventaris Unit Handphone (HP)</div>
  <table>
    <thead>
      <tr>
        <th class="c" style="width:16px">✓</th>
        <th style="width:20px">No</th>
        <th>Brand / Model</th>
        <th>Spesifikasi</th>
        <th>IMEI / SN</th>
        <th class="r">Harga Modal</th>
        <th class="c">Status</th>
        <th class="c">Tgl. Beli</th>
      </tr>
    </thead>
    <tbody>
      @php $no = 1; $totalModal = 0; @endphp
      @forelse($units as $u)
        @php
          $modal  = (float)$u->purchase_price;
          $totalModal += $modal;
          $status = $u->status->value;
        @endphp
        <tr>
          <td class="cb-cell"><span class="cb-box"></span></td>
          <td class="c muted">{{ $no++ }}</td>
          <td>
            <strong>{{ $u->model->brand->name ?? '—' }} {{ $u->model->name ?? '' }}</strong>
            <br><span class="muted">{{ ucfirst($u->unit_type->value) }}{{ $u->grade ? ' · Grade '.$u->grade : '' }}</span>
          </td>
          <td class="muted">{{ $u->ram }}/{{ $u->rom }} &nbsp; {{ $u->color }}</td>
          <td class="muted" style="font-size:7pt">{{ $u->imei ?: ($u->serial_number ?: '—') }}</td>
          <td class="r">Rp {{ number_format($modal, 0, ',', '.') }}</td>
          <td class="c">
            @if($status === 'ready')  <span class="badge badge-ready">Ready</span>
            @elseif($status === 'sold') <span class="badge badge-sold">Terjual</span>
            @else <span class="badge badge-retur">{{ ucfirst($status) }}</span>
            @endif
          </td>
          <td class="c muted">{{ $u->purchase_date ? $u->purchase_date->format('d/m/Y') : '—' }}</td>
        </tr>
      @empty
        <tr><td colspan="8" style="text-align:center;padding:14px;color:#9CA3AF">Tidak ada data unit</td></tr>
      @endforelse
      <tr class="total-row">
        <td colspan="5" style="text-align:right">TOTAL</td>
        <td class="r">Rp {{ number_format($totalModal, 0, ',', '.') }}</td>
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
        <th class="c" style="width:16px">✓</th>
        <th style="width:20px">No</th>
        <th>Nama Aksesoris</th>
        <th>Kategori</th>
        <th class="c">Stok</th>
        <th class="r">Harga Modal</th>
        <th class="r">Total Modal</th>
      </tr>
    </thead>
    <tbody>
      @php $ano = 1; $aTotalModal = 0; @endphp
      @foreach($accessories as $a)
        @php $aModal = (float)$a->purchase_price; $aTM = $aModal * $a->stock_qty; $aTotalModal += $aTM; @endphp
        <tr>
          <td class="cb-cell"><span class="cb-box"></span></td>
          <td class="c muted">{{ $ano++ }}</td>
          <td><strong>{{ $a->name }}</strong></td>
          <td class="muted">{{ $a->category ?: 'Lain-lain' }}</td>
          <td class="c">
            {{ $a->stock_qty }}
            @if($a->stock_qty <= 5) <span style="color:#B91C1C;font-size:7pt">(!)</span> @endif
          </td>
          <td class="r">Rp {{ number_format($aModal, 0, ',', '.') }}</td>
          <td class="r">Rp {{ number_format($aTM,    0, ',', '.') }}</td>
        </tr>
      @endforeach
      <tr class="total-row">
        <td colspan="4" style="text-align:right">TOTAL</td>
        <td class="c">{{ number_format($accQty, 0, ',', '.') }} pcs</td>
        <td></td>
        <td class="r">Rp {{ number_format($aTotalModal, 0, ',', '.') }}</td>
      </tr>
    </tbody>
  </table>
  @endif

  <div class="footer">Dokumen dicetak otomatis oleh sistem POS Alex Phone &mdash; {{ $printedAt }}</div>

</div>
</body>
</html>
