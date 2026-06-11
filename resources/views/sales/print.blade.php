<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Struk — {{ $sale->invoice_number }}</title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: 'Courier New', monospace; font-size: 12px; color: #000; background: #fff; padding: 16px; max-width: 320px; }
.center { text-align: center; }
.bold { font-weight: bold; }
.divider { border-top: 1px dashed #000; margin: 8px 0; }
.row { display: flex; justify-content: space-between; margin: 2px 0; }
.indent { margin-left: 12px; font-size: 11px; }
.total-row { font-weight: bold; font-size: 13px; }
</style>
</head>
<body>

<div class="center bold" style="font-size:15px;">ALEX PHONE</div>
<div class="center" style="font-size:10px;">Banjarnegara, Jawa Tengah</div>
<div class="center" style="font-size:10px;">WA: 0896-7488-6141</div>

<div class="divider"></div>

<div class="row"><span>No.</span><span class="bold">{{ $sale->invoice_number }}</span></div>
<div class="row"><span>Tgl</span><span>{{ $sale->sale_date->format('d/m/Y') }}</span></div>
<div class="row"><span>Kasir</span><span>{{ $sale->creator->name ?? '—' }}</span></div>
@if($sale->customer_name)
<div class="row"><span>Pembeli</span><span class="bold">{{ $sale->customer_name }}</span></div>
@endif
@if($sale->description)
<div style="margin-top:4px;font-size:10px;color:#333;">Ket: {{ $sale->description }}</div>
@endif

<div class="divider"></div>

@foreach($sale->items as $item)
<div>
    @if($item->unit_id)
        <div class="bold">{{ $item->unit->model->brand->name ?? '' }} {{ $item->unit->model->name ?? '' }}</div>
        <div class="indent">{{ $item->unit->ram ?? '' }}/{{ $item->unit->rom ?? '' }} {{ $item->unit->color ?? '' }}</div>
        @if($item->unit->imei)
        <div class="indent">IMEI: {{ $item->unit->imei }}</div>
        @endif
    @else
        <div class="bold">{{ $item->accessory->name ?? '' }} x{{ $item->quantity }}</div>
    @endif
    <div class="row indent">
        <span>Harga jual</span>
        <span>Rp {{ number_format($item->selling_price, 0, ',', '.') }}</span>
    </div>
</div>
@endforeach

<div class="divider"></div>

<div class="row total-row">
    <span>TOTAL</span>
    <span>Rp {{ number_format($sale->total_price, 0, ',', '.') }}</span>
</div>

<div class="divider"></div>

@foreach($sale->payments as $payment)
<div class="row">
    <span class="bold">{{ strtoupper($payment->method->value) }}</span>
    <span>Rp {{ number_format($payment->amount, 0, ',', '.') }}</span>
</div>
@endforeach

<div class="divider"></div>
<div class="center" style="margin-top:12px; font-size:10px;">Terima kasih telah berbelanja!</div>
<div class="center" style="font-size:10px;">Garansi servis 30 hari</div>

<script>window.onload = function(){ window.print(); }</script>
</body>
</html>
