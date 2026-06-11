@extends('layouts.app')
@section('title', 'Edit Transaksi — ' . $sale->invoice_number)

@section('content')
<div class="w-full">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-3">
            <a href="{{ route('sales.show', $sale) }}" class="flex items-center justify-center w-8 h-8 rounded-lg transition-colors"
               style="background:var(--bg-soft);color:var(--ink-mute)"
               onmouseenter="this.style.background='var(--line)'" onmouseleave="this.style.background='var(--bg-soft)'">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h2 class="text-lg font-semibold leading-none font-mono" style="color:var(--ink)">{{ $sale->invoice_number }}</h2>
                <p class="text-xs mt-1" style="color:var(--ink-mute)">Edit transaksi — oleh {{ $sale->creator->name ?? '—' }}</p>
            </div>
        </div>
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
                Hapus Transaksi
            </button>
        </form>
    </div>

    <form method="POST" action="{{ route('sales.update', $sale) }}" id="edit-form">
        @csrf @method('PUT')

        <div class="grid lg:grid-cols-3 gap-5">

            {{-- Left: items --}}
            <div class="lg:col-span-2 space-y-5">

                {{-- Tanggal --}}
                <div class="bg-white rounded-xl border overflow-hidden" style="border-color:var(--line)">
                    <div class="px-5 py-3.5" style="border-bottom:1px solid var(--line);background:var(--bg-soft)">
                        <span class="text-[11px] font-medium uppercase tracking-widest font-mono" style="color:var(--ink-mute)">Info Transaksi</span>
                    </div>
                    <div class="p-5">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="field-label">Tanggal Transaksi <span style="color:var(--warn)">*</span></label>
                                <input type="date" name="sale_date" value="{{ old('sale_date', $sale->sale_date->toDateString()) }}"
                                       required class="field-input @error('sale_date') error @enderror" />
                                @error('sale_date')<p class="field-error">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="field-label">Status</label>
                                <div class="flex items-center h-10">
                                    @if($sale->status->value === 'approved')
                                        <span class="px-3 py-1.5 rounded-full text-xs font-semibold" style="background:#F0FDF4;color:var(--success)">Approved</span>
                                    @else
                                        <span class="px-3 py-1.5 rounded-full text-xs font-semibold" style="background:#FFFBEB;color:#B45309;border:1px solid #FDE68A">Pending</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="mt-4">
                            <label class="field-label">Keterangan <span class="text-[10px] font-normal" style="color:var(--ink-mute)">(opsional)</span></label>
                            <textarea name="description" rows="2"
                                      placeholder="Catatan atau keterangan tambahan transaksi..."
                                      class="field-input @error('description') error @enderror"
                                      style="width:100%;resize:none">{{ old('description', $sale->description) }}</textarea>
                            @error('description')<p class="field-error">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>

                {{-- Item table --}}
                <div class="bg-white rounded-xl border overflow-hidden" style="border-color:var(--line)">
                    <div class="px-5 py-3.5" style="border-bottom:1px solid var(--line);background:var(--bg-soft)">
                        <span class="text-[11px] font-medium uppercase tracking-widest font-mono" style="color:var(--ink-mute)">Item Produk</span>
                    </div>
                    <table class="w-full text-sm">
                        <thead>
                            <tr style="border-bottom:1px solid var(--line)">
                                <th class="text-left px-5 py-2.5 text-[11px] font-medium uppercase tracking-wider font-mono" style="color:var(--ink-mute)">Produk</th>
                                <th class="text-right px-4 py-2.5 text-[11px] font-medium uppercase tracking-wider font-mono" style="color:var(--ink-mute)">Harga Beli</th>
                                <th class="text-right px-4 py-2.5 text-[11px] font-medium uppercase tracking-wider font-mono" style="color:var(--ink-mute)">Harga Jual</th>
                                <th class="text-right px-4 py-2.5 text-[11px] font-medium uppercase tracking-wider font-mono" style="color:var(--ink-mute)">Qty</th>
                                <th class="text-right px-5 py-2.5 text-[11px] font-medium uppercase tracking-wider font-mono" style="color:var(--ink-mute)">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sale->items as $item)
                            <tr style="border-bottom:1px solid var(--line)" data-qty="{{ $item->quantity }}" data-buy="{{ $item->purchase_price }}">
                                <td class="px-5 py-3.5">
                                    @if($item->unit_id)
                                        <div class="font-semibold" style="color:var(--ink)">{{ $item->unit->model->brand->name ?? '—' }} {{ $item->unit->model->name ?? '—' }}</div>
                                        <div class="text-xs font-mono mt-0.5" style="color:var(--ink-mute)">IMEI: {{ $item->unit->imei ?? '-' }}</div>
                                    @else
                                        <div class="font-semibold" style="color:var(--ink)">{{ $item->accessory->name ?? '—' }}</div>
                                        <div class="text-xs mt-0.5" style="color:var(--ink-mute)">Qty: {{ $item->quantity }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 text-right font-mono tabular-nums text-xs" style="color:var(--ink-mute)">
                                    Rp {{ number_format($item->purchase_price, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3.5 text-right" style="min-width:140px">
                                    <div class="money-wrap" style="justify-content:flex-end">
                                        <span class="rp-prefix">Rp</span>
                                        <input type="text"
                                               name="items[{{ $item->id }}][selling_price]"
                                               class="field-input money-input item-sell text-right"
                                               value="{{ old('items.'.$item->id.'.selling_price', (int) $item->selling_price) }}"
                                               style="min-width:100px"
                                               inputmode="numeric" />
                                    </div>
                                </td>
                                <td class="px-4 py-3.5 text-right font-mono tabular-nums" style="color:var(--ink-mute)">{{ $item->quantity }}</td>
                                <td class="px-5 py-3.5 text-right font-bold font-mono tabular-nums item-subtotal" style="color:var(--ink)">
                                    Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot style="border-top:2px solid var(--line)">
                            <tr>
                                <td colspan="4" class="px-5 py-3 text-right text-sm font-semibold" style="color:var(--ink-soft)">Total</td>
                                <td class="px-5 py-3 text-right font-bold font-mono text-base tabular-nums" id="total-display" style="color:var(--ink)">
                                    Rp {{ number_format($sale->total_price, 0, ',', '.') }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

            </div>

            {{-- Right: payments summary + actions --}}
            <div class="space-y-5">

                {{-- Pembayaran (read-only) --}}
                <div class="bg-white rounded-xl border p-5" style="border-color:var(--line)">
                    <h3 class="text-sm font-semibold mb-4" style="color:var(--ink)">Rincian Pembayaran</h3>
                    <div class="space-y-2">
                        @foreach($sale->payments as $payment)
                        <div class="flex items-center justify-between py-2.5 px-3 rounded-xl"
                             style="{{ $payment->method->value === 'utang' ? 'background:#FFF5F5;border:1px solid #FFE4E4' : 'background:var(--bg-soft);border:1px solid var(--line)' }}">
                            <span class="text-sm font-medium capitalize" style="color:var(--ink)">{{ $payment->method->value }}</span>
                            <span class="font-semibold font-mono tabular-nums text-sm"
                                  style="color:{{ $payment->method->value === 'utang' ? 'var(--warn)' : 'var(--ink)' }}">
                                Rp {{ number_format($payment->amount, 0, ',', '.') }}
                            </span>
                        </div>
                        @endforeach
                    </div>
                    <p class="text-xs mt-3" style="color:var(--ink-mute)">Pembayaran tidak dapat diubah melalui form ini.</p>
                </div>

                {{-- Actions --}}
                <div class="space-y-2.5">
                    <button type="submit" class="btn-primary w-full" style="height:44px;font-size:14px">Simpan Perubahan</button>
                    <a href="{{ route('sales.show', $sale) }}" class="btn-secondary w-full" style="height:44px;font-size:14px">Batal</a>
                </div>

            </div>

        </div>
    </form>

</div>

@include('components.money-format')
<script>
(function () {
    function rawVal(el) {
        return parseInt((el.value || '').replace(/[^0-9]/g, ''), 10) || 0;
    }
    function recalc() {
        var total = 0;
        document.querySelectorAll('tbody tr[data-qty]').forEach(function (row) {
            var sellEl    = row.querySelector('.item-sell');
            var subtotEl  = row.querySelector('.item-subtotal');
            var qty       = parseInt(row.dataset.qty, 10) || 1;
            var sell      = rawVal(sellEl);
            var subtotal  = sell * qty;
            total += subtotal;
            subtotEl.textContent = 'Rp ' + subtotal.toLocaleString('id-ID');
        });
        document.getElementById('total-display').textContent = 'Rp ' + total.toLocaleString('id-ID');
    }
    document.querySelectorAll('.item-sell').forEach(function (el) {
        el.addEventListener('input', recalc);
    });
})();
</script>
@endsection
