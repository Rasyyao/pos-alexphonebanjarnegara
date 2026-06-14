@extends('layouts.app')
@section('title', $accessory->name . ' — Detail Aksesoris')

@section('content')
<div class="w-full">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-3">
            <a href="{{ route('accessories.index') }}" class="flex items-center justify-center w-8 h-8 rounded-lg transition-colors"
               style="background:var(--bg-soft);color:var(--ink-mute)"
               onmouseenter="this.style.background='var(--line)'" onmouseleave="this.style.background='var(--bg-soft)'">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h2 class="text-lg font-semibold leading-none" style="color:var(--ink)">{{ $accessory->name }}</h2>
                <p class="text-xs mt-1" style="color:var(--ink-mute)">{{ $accessory->category ?? 'Tanpa kategori' }}</p>
            </div>
        </div>
        <a href="{{ route('accessories.edit', $accessory) }}" class="btn-primary" style="height:36px;padding:0 16px;font-size:13px">
            Edit Aksesoris
        </a>
    </div>

    <div class="grid lg:grid-cols-3 gap-5">

        {{-- Left: main details (col-span-2) --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- Informasi Produk --}}
            <div class="bg-white rounded-xl border overflow-hidden" style="border-color:var(--line)">
                <div class="px-5 py-3.5 flex items-center gap-2" style="border-bottom:1px solid var(--line);background:var(--bg-soft)">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" style="color:var(--ink-mute)">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                    <span class="text-[11px] font-medium uppercase tracking-widest font-mono" style="color:var(--ink-mute)">Informasi Produk</span>
                </div>
                <div class="divide-y">
                    <div class="flex items-center px-5 py-3" style="border-color:var(--line)">
                        <span class="w-36 text-xs font-medium flex-shrink-0" style="color:var(--ink-mute)">Nama Aksesoris</span>
                        <span class="text-sm font-medium" style="color:var(--ink)">{{ $accessory->name }}</span>
                    </div>
                    <div class="flex items-center px-5 py-3" style="border-color:var(--line)">
                        <span class="w-36 text-xs font-medium flex-shrink-0" style="color:var(--ink-mute)">Kategori</span>
                        <span class="text-sm font-medium" style="color:var(--ink)">{{ $accessory->category ?? '—' }}</span>
                    </div>
                    <div class="flex items-center px-5 py-3" style="border-color:var(--line)">
                        <span class="w-36 text-xs font-medium flex-shrink-0" style="color:var(--ink-mute)">Stok</span>
                        <span class="text-sm font-semibold font-mono tabular-nums"
                              style="color:{{ $accessory->stock_qty <= 3 ? 'var(--warn)' : 'var(--ink)' }}">
                            {{ $accessory->stock_qty }}
                            @if($accessory->stock_qty <= 3 && $accessory->stock_qty > 0)
                                <span class="ml-1.5 text-[11px] font-medium" style="color:var(--warn)">— Stok menipis</span>
                            @elseif($accessory->stock_qty === 0)
                                <span class="ml-1.5 text-[11px] font-medium" style="color:var(--warn)">— Habis</span>
                            @endif
                        </span>
                    </div>
                    <div class="flex items-center px-5 py-3" style="border-color:var(--line)">
                        <span class="w-36 text-xs font-medium flex-shrink-0" style="color:var(--ink-mute)">Tanggal Input</span>
                        <span class="text-sm font-mono" style="color:var(--ink)">{{ $accessory->created_at->format('d M Y, H:i') }}</span>
                    </div>
                </div>
            </div>

            {{-- Harga --}}
            <div class="bg-white rounded-xl border overflow-hidden" style="border-color:var(--line)">
                <div class="px-5 py-3.5 flex items-center gap-2" style="border-bottom:1px solid var(--line);background:var(--bg-soft)">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" style="color:var(--ink-mute)">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="text-[11px] font-medium uppercase tracking-widest font-mono" style="color:var(--ink-mute)">Harga</span>
                </div>
                <div class="divide-y">
                    <div class="flex items-center px-5 py-3" style="border-color:var(--line)">
                        <span class="w-36 text-xs font-medium flex-shrink-0" style="color:var(--ink-mute)">Harga Beli</span>
                        <span class="text-sm font-mono tabular-nums" style="color:var(--ink-soft)">Rp {{ number_format($accessory->purchase_price, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center px-5 py-3" style="border-color:var(--line)">
                        <span class="w-36 text-xs font-medium flex-shrink-0" style="color:var(--ink-mute)">Bayar Dari</span>
                        <span class="text-sm font-semibold font-mono" style="color:var(--ink-soft)">
                            {{ $accessory->purchase_payment_method === 'transfer' ? 'Transfer / ATM' : 'Kas Tunai' }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Rekap Pembayaran Penjualan --}}
            <div class="bg-white rounded-xl border overflow-hidden" style="border-color:var(--line)">
                <div class="px-5 py-3.5 flex items-center justify-between" style="border-bottom:1px solid var(--line);background:var(--bg-soft)">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" style="color:var(--ink-mute)">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        <span class="text-[11px] font-medium uppercase tracking-widest font-mono" style="color:var(--ink-mute)">Rekap Pembayaran Penjualan</span>
                    </div>
                    @if($saleHistory->count() > 0)
                    @php
                        $totalQtySold = $saleHistory->sum('quantity');
                        $totalRevenue = $saleHistory->sum('subtotal');
                    @endphp
                    <div class="flex items-center gap-3">
                        <span class="text-[11px] font-mono" style="color:var(--ink-mute)">{{ $totalQtySold }} terjual</span>
                        <span class="text-[11px] font-bold font-mono tabular-nums" style="color:var(--success)">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</span>
                    </div>
                    @endif
                </div>

                @if($saleHistory->isEmpty())
                <div class="px-5 py-8 text-center">
                    <p class="text-xs" style="color:var(--ink-mute)">Belum ada penjualan untuk aksesoris ini.</p>
                </div>
                @else
                <div class="overflow-x-auto">
                    <table class="w-full text-xs">
                        <thead>
                            <tr style="background:var(--bg-soft);border-bottom:1px solid var(--line)">
                                <th class="text-left px-5 py-2.5 font-bold uppercase tracking-wider font-mono" style="color:var(--ink-mute)">Invoice</th>
                                <th class="text-left px-4 py-2.5 font-bold uppercase tracking-wider font-mono" style="color:var(--ink-mute)">Tanggal</th>
                                <th class="text-left px-4 py-2.5 font-bold uppercase tracking-wider font-mono" style="color:var(--ink-mute)">Pembeli</th>
                                <th class="text-center px-4 py-2.5 font-bold uppercase tracking-wider font-mono" style="color:var(--ink-mute)">Qty</th>
                                <th class="text-right px-4 py-2.5 font-bold uppercase tracking-wider font-mono" style="color:var(--ink-mute)">Subtotal</th>
                                <th class="text-left px-5 py-2.5 font-bold uppercase tracking-wider font-mono" style="color:var(--ink-mute)">Pembayaran</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($saleHistory as $item)
                            <tr style="border-bottom:1px solid var(--line)"
                                onmouseenter="this.style.background='var(--bg-soft)'" onmouseleave="this.style.background=''">
                                <td class="px-5 py-3 font-mono font-medium" style="color:var(--ink)">
                                    <a href="{{ route('sales.show', $item->sale) }}" style="color:var(--accent)" class="hover:underline">
                                        {{ $item->sale->invoice_number }}
                                    </a>
                                </td>
                                <td class="px-4 py-3 font-mono" style="color:var(--ink-soft)">{{ $item->sale->sale_date->format('d/m/Y') }}</td>
                                <td class="px-4 py-3" style="color:var(--ink-soft)">
                                    {{ $item->sale->customer_name ?? $item->sale->creator->name ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-center font-mono font-semibold" style="color:var(--ink)">{{ $item->quantity }}</td>
                                <td class="px-4 py-3 text-right font-mono font-semibold tabular-nums" style="color:var(--ink)">
                                    Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                </td>
                                <td class="px-5 py-3">
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($item->sale->payments as $payment)
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold font-mono"
                                              style="{{ $payment->method->value === 'utang'
                                                  ? 'background:#FFF5F5;color:var(--warn)'
                                                  : ($payment->method->value === 'transfer'
                                                      ? 'background:#EFF6FF;color:var(--accent)'
                                                      : 'background:#F0FDF4;color:var(--success)') }}">
                                            {{ strtoupper($payment->method->value) }}
                                            Rp {{ number_format($payment->amount, 0, ',', '.') }}
                                        </span>
                                        @endforeach
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>

        </div>

        {{-- Right sidebar: margin + actions --}}
        <div class="space-y-5">

            {{-- Harga Jual --}}
            <div class="bg-white rounded-xl border overflow-hidden" style="border-color:var(--line)">
                <div class="px-5 py-3.5" style="border-bottom:1px solid var(--line);background:var(--bg-soft)">
                    <span class="text-[11px] font-medium uppercase tracking-widest font-mono" style="color:var(--ink-mute)">Harga Jual</span>
                </div>
                <div class="p-5">
                    <label class="field-label">Harga Jual</label>
                    <div class="money-wrap">
                        <span class="rp-prefix">Rp</span>
                        <input type="text" id="show-est-jual" class="field-input money-input" placeholder="0" inputmode="numeric" />
                    </div>
                    <div class="mt-3 pt-3 border-t" style="border-color:var(--line)">
                        <div class="text-[10px] font-bold uppercase tracking-widest font-mono mb-1" style="color:var(--ink-mute)">Estimasi Margin</div>
                        <div class="text-xl font-bold font-mono tabular-nums" id="show-margin-amount" style="color:var(--ink-mute)">Rp 0</div>
                        <div class="text-xs mt-0.5" id="show-margin-pct" style="color:var(--ink-mute)">Isi harga jual untuk lihat margin</div>
                        <div class="mt-2 h-1.5 rounded-full overflow-hidden" style="background:var(--bg-soft)">
                            <div id="show-margin-bar" class="h-full rounded-full transition-all duration-300" style="width:0%;background:var(--success)"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="space-y-2.5">
                <a href="{{ route('accessories.edit', $accessory) }}" class="btn-primary w-full" style="height:44px;font-size:14px">
                    Edit Aksesoris
                </a>
                <a href="{{ route('accessories.index') }}" class="btn-secondary w-full" style="height:44px;font-size:14px">
                    Kembali ke Daftar
                </a>
            </div>

        </div>

    </div>
</div>

@include('components.money-format')
<script>
(function() {
    function rawInt(id) {
        return parseInt((document.getElementById(id)?.value || '').replace(/[^0-9]/g, ''), 10) || 0;
    }
    var BUY = {{ (int)$accessory->purchase_price }};
    function calcShowMargin() {
        var sell   = rawInt('show-est-jual');
        var margin = sell - BUY;
        var amtEl  = document.getElementById('show-margin-amount');
        var pctEl  = document.getElementById('show-margin-pct');
        var bar    = document.getElementById('show-margin-bar');
        if (sell > 0) {
            var pct   = Math.round((margin / sell) * 100);
            var color = margin >= 0 ? 'var(--success)' : 'var(--warn)';
            amtEl.textContent = 'Rp ' + margin.toLocaleString('id-ID');
            amtEl.style.color = color;
            pctEl.textContent = (margin >= 0 ? 'Untung ' : 'Rugi ') + Math.abs(pct) + '% dari harga jual';
            pctEl.style.color = color;
            bar.style.width = Math.max(0, Math.min(100, Math.abs(pct))) + '%';
            bar.style.background = color;
        } else {
            amtEl.textContent = 'Rp 0';
            amtEl.style.color = 'var(--ink-mute)';
            pctEl.textContent = 'Isi harga jual untuk lihat margin';
            pctEl.style.color = 'var(--ink-mute)';
            bar.style.width = '0%';
        }
    }
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('show-est-jual')?.addEventListener('input', calcShowMargin);
    });
})();
</script>
@endsection
