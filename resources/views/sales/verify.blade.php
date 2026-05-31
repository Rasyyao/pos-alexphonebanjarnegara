@extends('layouts.app')
@section('title', 'Verifikasi Penjualan')

@section('content')
<div class="w-full space-y-5">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold" style="color:var(--ink)">Verifikasi Penjualan</h2>
            <p class="text-xs mt-0.5" style="color:var(--ink-mute)">Transaksi pending menunggu persetujuan superadmin. Stok baru berkurang setelah di-approve.</p>
        </div>
        <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-semibold"
              style="background:#FFFBEB;color:#B45309;border:1px solid #FDE68A">
            {{ $pending->total() }} menunggu
        </span>
    </div>

    <div class="bg-white rounded-xl border overflow-hidden shadow-sm" style="border-color:var(--line)">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr style="background:var(--bg-soft); border-bottom:1px solid var(--line)">
                        <th class="text-left px-5 py-3.5 text-[11px] font-medium uppercase tracking-wider font-mono" style="color:var(--ink-mute)">Invoice</th>
                        <th class="text-left px-4 py-3.5 text-[11px] font-medium uppercase tracking-wider font-mono" style="color:var(--ink-mute)">Daftar Item</th>
                        <th class="text-left px-4 py-3.5 text-[11px] font-medium uppercase tracking-wider font-mono" style="color:var(--ink-mute)">Pembayaran</th>
                        <th class="text-right px-4 py-3.5 text-[11px] font-medium uppercase tracking-wider font-mono" style="color:var(--ink-mute)">Total & Laba</th>
                        <th class="w-56 px-5 py-3.5"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pending as $sale)
                    <tr class="transition-colors" style="border-bottom:1px solid var(--line)"
                        onmouseenter="this.style.background='var(--bg-soft)'" onmouseleave="this.style.background=''">
                        
                        {{-- Invoice --}}
                        <td class="px-5 py-4 align-top">
                            <div class="font-bold font-mono text-[13px]" style="color:var(--ink)">
                                {{ $sale->invoice_number }}
                            </div>
                            <span class="inline-block px-2 py-0.5 mt-1 rounded-full text-[10px] font-medium" 
                                  style="background:#FFFBEB;color:#B45309">
                                Pending
                            </span>
                            <div class="text-[11px] mt-2" style="color:var(--ink-mute)">
                                {{ $sale->sale_date->isoFormat('D MMM YYYY') }}
                                <span class="block mt-0.5 font-medium text-[10px]" style="color:var(--ink-soft)">Kasir: {{ $sale->creator->name ?? '—' }}</span>
                            </div>
                        </td>

                        {{-- Daftar Item --}}
                        <td class="px-4 py-4 align-top">
                            <div class="space-y-2">
                                @foreach($sale->items as $item)
                                <div class="text-xs">
                                    <div class="font-medium" style="color:var(--ink)">
                                        @if($item->unit_id)
                                            {{ $item->unit->model->brand->name ?? '—' }} {{ $item->unit->model->name ?? '' }}
                                            @if($item->unit->imei)
                                                <span class="text-[10px] font-mono text-gray-500 block mt-0.5">IMEI: {{ $item->unit->imei }}</span>
                                            @elseif($item->unit->serial_number)
                                                <span class="text-[10px] font-mono text-gray-500 block mt-0.5 font-medium">SN: {{ $item->unit->serial_number }}</span>
                                            @else
                                                <span class="text-[10px] text-gray-400 block mt-0.5">No IMEI/SN</span>
                                            @endif
                                        @else
                                            {{ $item->accessory->name ?? '—' }}
                                            <span class="text-[11px] font-mono text-gray-400 font-semibold">×{{ $item->quantity }}</span>
                                        @endif
                                    </div>
                                    <div class="text-[10px] font-mono mt-0.5" style="color:var(--ink-mute)">
                                        Subtotal: Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                    </div>
                                </div>
                                @if(!$loop->last)
                                <hr class="border-gray-100 my-1.5" />
                                @endif
                                @endforeach
                            </div>
                        </td>

                        {{-- Pembayaran --}}
                        <td class="px-4 py-4 align-top">
                            <div class="flex flex-col gap-1.5">
                                @foreach($sale->payments as $payment)
                                <div class="inline-flex items-center gap-1.5 px-2 py-1 rounded-lg text-[11px] font-medium w-fit"
                                     style="{{ $payment->method->value === 'utang' ? 'background:#FFF5F5;color:var(--warn)' : 'background:var(--bg-soft);color:var(--ink-soft)' }}">
                                    <span class="capitalize font-semibold">{{ $payment->method->value }}</span>
                                    <span class="font-mono">Rp {{ number_format($payment->amount, 0, ',', '.') }}</span>
                                </div>
                                @endforeach
                            </div>
                        </td>

                        {{-- Total & Laba --}}
                        <td class="px-4 py-4 text-right align-top">
                            <div class="font-bold font-mono text-sm" style="color:var(--ink)">
                                Rp {{ number_format($sale->total_price, 0, ',', '.') }}
                            </div>
                            <div class="text-[11px] font-mono font-semibold mt-1" style="color:var(--success)">
                                Est. Laba: Rp {{ number_format($sale->profit, 0, ',', '.') }}
                            </div>
                        </td>

                        {{-- Aksi --}}
                        <td class="px-5 py-4 align-top">
                            <div class="flex items-center justify-end gap-1.5">
                                <form method="POST" action="{{ route('sales.approve', $sale) }}"
                                      onsubmit="return confirm('Approve transaksi {{ $sale->invoice_number }}? Stok akan berkurang.')">
                                    @csrf
                                    <button type="submit" class="btn-primary" style="height:32px;padding:0 12px;font-size:12px;border-radius:8px">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                        Verifikasi
                                    </button>
                                </form>
                                <a href="{{ route('sales.show', $sale) }}"
                                   title="Lihat Detail"
                                   class="inline-flex items-center justify-center w-8 h-8 rounded-lg transition-colors"
                                   style="background:var(--bg-soft);color:var(--ink-soft)"
                                   onmouseenter="this.style.background='#E4E9F2'" onmouseleave="this.style.background='var(--bg-soft)'">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                                <form method="POST" action="{{ route('sales.destroy', $sale) }}"
                                      onsubmit="return confirm('Hapus transaksi {{ $sale->invoice_number }}? Aksi ini tidak dapat dibatalkan.')">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                            title="Hapus"
                                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg transition-colors"
                                            style="background:#FFF5F5;color:var(--warn)"
                                            onmouseenter="this.style.background='#FEE2E2'" onmouseleave="this.style.background='#FFF5F5'">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>

                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-5 py-14 text-center">
                            <div class="w-12 h-12 mx-auto rounded-full flex items-center justify-center mb-3" style="background:#F0FDF4;color:var(--success)">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <p class="text-sm font-medium" style="color:var(--ink)">Tidak ada transaksi menunggu verifikasi</p>
                            <p class="text-xs mt-1" style="color:var(--ink-mute)">Semua penjualan sudah diverifikasi.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-5 py-3 flex items-center justify-between" style="border-top:1px solid var(--line);background:var(--bg-soft)">
            <span class="text-xs font-mono" style="color:var(--ink-mute)">
                @if($pending->total() > 0)
                    {{ $pending->firstItem() }}–{{ $pending->lastItem() }} dari {{ $pending->total() }} transaksi
                @else
                    0 transaksi
                @endif
            </span>
            {{ $pending->links() }}
        </div>
    </div>

</div>
@endsection
