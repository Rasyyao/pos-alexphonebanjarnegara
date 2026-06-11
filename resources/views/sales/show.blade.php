@extends('layouts.app')
@section('title', 'Detail Transaksi')

@section('content')
<div class="w-full space-y-5">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('sales.index') }}"
               class="w-9 h-9 rounded-xl flex items-center justify-center border transition-colors"
               style="border-color:var(--line);color:var(--ink-mute);background:var(--bg)"
               onmouseenter="this.style.borderColor='var(--ink)';this.style.color='var(--ink)'"
               onmouseleave="this.style.borderColor='var(--line)';this.style.color='var(--ink-mute)'">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h2 class="text-base font-bold font-mono" style="color:var(--ink)">{{ $sale->invoice_number }}</h2>
                <p class="text-xs mt-0.5" style="color:var(--ink-mute)">
                    {{ $sale->sale_date->isoFormat('D MMMM YYYY') }} · oleh {{ $sale->creator->name ?? '—' }}
                    @if($sale->customer_name)
                        · <span class="font-medium" style="color:var(--ink)">{{ $sale->customer_name }}</span>
                    @endif
                </p>
                @if($sale->description)
                <p class="text-xs mt-1 max-w-md" style="color:var(--ink-soft)">{{ $sale->description }}</p>
                @endif
            </div>
        </div>
        <div class="flex items-center gap-2">
            @if($sale->status->value === 'pending')
                <span class="px-3 py-1.5 rounded-full text-xs font-semibold" style="background:#FFFBEB;color:#B45309;border:1px solid #FDE68A">Pending</span>
                @if(auth()->user()->role->value === 'superadmin')
                <form method="POST" action="{{ route('sales.approve', $sale) }}">
                    @csrf
                    <button type="submit" class="btn-primary" style="height:36px;padding:0 16px;font-size:13px">Approve</button>
                </form>
                @endif
            @else
                <span class="px-3 py-1.5 rounded-full text-xs font-semibold" style="background:#F0FDF4;color:var(--success)">Approved</span>
            @endif
            <a href="{{ route('sales.print', $sale) }}" target="_blank" class="btn-secondary" style="height:36px;padding:0 16px;font-size:13px">
                <svg class="w-4 h-4 inline mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Cetak Struk
            </a>
            @if(auth()->user()->role->value === 'superadmin')
            <a href="{{ route('sales.edit', $sale) }}"
               class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-medium transition-colors"
               style="background:#EFF6FF;color:#1D4ED8;border:1px solid #BFDBFE"
               onmouseenter="this.style.background='#DBEAFE'" onmouseleave="this.style.background='#EFF6FF'">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Edit
            </a>
            <form method="POST" action="{{ route('sales.destroy', $sale) }}"
                  onsubmit="return confirm('Hapus transaksi {{ $sale->invoice_number }}?{{ $sale->status->value === "approved" ? " Stok akan dikembalikan." : "" }}')">
                @csrf @method('DELETE')
                <button type="submit"
                        class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-medium transition-colors"
                        style="background:#FFF5F5;color:var(--warn);border:1px solid #FEE2E2"
                        onmouseenter="this.style.background='#FEE2E2'" onmouseleave="this.style.background='#FFF5F5'">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Hapus
                </button>
            </form>
            @endif
        </div>
    </div>

    <div class="grid lg:grid-cols-3 gap-5">

        {{-- Left: Items --}}
        <div class="lg:col-span-2 space-y-5">
            {{-- Items --}}
            <div class="bg-white rounded-xl border overflow-hidden" style="border-color:var(--line)">
                <div class="px-5 py-3.5" style="border-bottom:1px solid var(--line)">
                    <h3 class="text-sm font-semibold" style="color:var(--ink)">Item Produk</h3>
                </div>
                <table class="w-full text-sm">
                    <thead>
                        <tr style="background:#ffffff; border-bottom:1px solid var(--line)">
                            <th class="text-left px-5 py-2.5 text-[11px] font-medium uppercase tracking-wider font-mono" style="color:var(--ink-mute)">Produk</th>
                            <th class="text-right px-4 py-2.5 text-[11px] font-medium uppercase tracking-wider font-mono" style="color:var(--ink-mute)">Harga Beli</th>
                            <th class="text-right px-4 py-2.5 text-[11px] font-medium uppercase tracking-wider font-mono" style="color:var(--ink-mute)">Harga Jual</th>
                            <th class="text-right px-5 py-2.5 text-[11px] font-medium uppercase tracking-wider font-mono" style="color:var(--ink-mute)">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sale->items as $item)
                        <tr class="transition-colors" style="border-bottom:1px solid var(--line)"
                            onmouseenter="this.style.background='var(--bg-soft)'" onmouseleave="this.style.background=''">
                            <td class="px-5 py-3.5">
                                @if($item->unit_id)
                                    <div class="font-semibold" style="color:var(--ink)">{{ $item->unit->model->brand->name ?? '—' }} {{ $item->unit->model->name ?? '—' }}</div>
                                    <div class="text-xs font-mono mt-0.5" style="color:var(--ink-mute)">IMEI: {{ $item->unit->imei ?? '-' }}</div>
                                @else
                                    <div class="font-semibold" style="color:var(--ink)">{{ $item->accessory->name ?? '—' }}</div>
                                    <div class="text-xs mt-0.5" style="color:var(--ink-mute)">Qty: {{ $item->quantity }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-right font-mono tabular-nums" style="color:var(--ink-mute)">Rp {{ number_format($item->purchase_price, 0, ',', '.') }}</td>
                            <td class="px-4 py-3.5 text-right font-mono tabular-nums" style="color:var(--ink-soft)">Rp {{ number_format($item->selling_price, 0, ',', '.') }}</td>
                            <td class="px-5 py-3.5 text-right font-bold font-mono tabular-nums" style="color:var(--ink)">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot style="border-top:2px solid var(--line); background:#ffffff">
                        <tr>
                            <td colspan="3" class="px-5 py-3 text-right text-sm font-semibold" style="color:var(--ink-soft)">Total</td>
                            <td class="px-5 py-3 text-right font-bold font-mono text-base tabular-nums" style="color:var(--ink)">Rp {{ number_format($sale->total_price, 0, ',', '.') }}</td>
                        </tr>
                        @if($sale->status->value === 'approved')
                        <tr>
                            <td colspan="3" class="px-5 py-2 text-right text-sm font-medium" style="color:var(--success)">Laba Bersih</td>
                            <td class="px-5 py-2 text-right font-semibold font-mono tabular-nums" style="color:var(--success)">Rp {{ number_format($sale->profit, 0, ',', '.') }}</td>
                        </tr>
                        @endif
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- Right: Payments & Debt --}}
        <div class="space-y-5">
            {{-- Estimasi Margin --}}
            @php
                $margin = $sale->profit;
                $marginPct = $sale->total_price > 0 ? round(($margin / $sale->total_price) * 100) : 0;
                $marginColor = $margin >= 0 ? 'var(--success)' : 'var(--warn)';
                $barWidth = max(0, min(100, $marginPct));
            @endphp
            <div class="bg-white rounded-xl border overflow-hidden" style="border-color:var(--line)">
                <div class="px-5 py-3.5" style="border-bottom:1px solid var(--line);background:var(--bg-soft)">
                    <span class="text-[11px] font-medium uppercase tracking-widest font-mono" style="color:var(--ink-mute)">Estimasi Margin</span>
                </div>
                <div class="p-5">
                    <div class="text-3xl font-semibold font-mono tabular-nums" style="color:{{ $marginColor }}">
                        Rp {{ number_format($margin, 0, ',', '.') }}
                    </div>
                    <div class="text-xs mt-1" style="color:{{ $marginColor }}">
                        {{ $margin >= 0 ? 'Untung' : 'Rugi' }} {{ abs($marginPct) }}% dari total penjualan
                    </div>
                    <div class="mt-4 h-1.5 rounded-full overflow-hidden" style="background:var(--bg-soft)">
                        <div class="h-full rounded-full" style="width:{{ $barWidth }}%;background:{{ $marginColor }}"></div>
                    </div>
                </div>
            </div>

            {{-- Payments --}}
            <div class="bg-white rounded-xl border p-5" style="border-color:var(--line)">
                <h3 class="text-sm font-semibold mb-4" style="color:var(--ink)">Rincian Pembayaran</h3>
                <div class="space-y-2">
                    @foreach($sale->payments as $payment)
                    <div class="flex items-center justify-between py-3 px-4 rounded-xl"
                         style="{{ $payment->method->value === 'utang' ? 'background:#FFF5F5;border:1px solid #FFE4E4' : 'background:var(--bg-soft);border:1px solid var(--line)' }}">
                        <span class="text-sm font-medium capitalize" style="color:var(--ink)">{{ $payment->method->value }}</span>
                        <span class="font-semibold font-mono tabular-nums"
                              style="color:{{ $payment->method->value === 'utang' ? 'var(--warn)' : 'var(--ink)' }}">
                            Rp {{ number_format($payment->amount, 0, ',', '.') }}
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>

            @if($sale->debt)
            <div class="flex items-center justify-between p-5 rounded-xl" style="background:#FFF5F5;border:1px solid #FFE4E4">
                <div>
                    <h3 class="text-sm font-semibold" style="color:var(--warn)">Utang Aktif</h3>
                    <p class="text-xs mt-0.5" style="color:var(--ink-mute)">Status: <span class="font-medium capitalize">{{ $sale->debt->status }}</span></p>
                </div>
                <span class="font-bold font-mono text-lg tabular-nums" style="color:var(--warn)">Rp {{ number_format($sale->debt->amount, 0, ',', '.') }}</span>
            </div>
            @endif
        </div>

    </div>

</div>
@endsection
