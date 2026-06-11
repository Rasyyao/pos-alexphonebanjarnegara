<div>
    {{-- Filter bar --}}
    <div class="bg-white rounded-xl border p-4 mb-4 flex flex-wrap gap-2 animate-fade-in" style="border-color:var(--line)">
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari invoice..." class="field-input" style="width:200px;height:36px;padding:0 10px;font-size:13px" />

        <select wire:model.live="status" class="field-input" style="width:auto;height:36px;padding:0 10px;font-size:13px;min-width:140px">
            <option value="">Semua Status</option>
            <option value="pending">Pending</option>
            <option value="approved">Approved</option>
            <option value="cancelled">Cancelled</option>
        </select>

        <input wire:model.live="date" type="date" class="field-input" style="width:auto;height:36px;padding:0 10px;font-size:13px" />

        <button wire:click="resetFilters" class="btn-secondary" style="height:36px;padding:0 14px;font-size:13px">Reset</button>
    </div>

    {{-- Loading skeleton --}}
    <div wire:loading wire:target="search,status,date,resetFilters"
         class="bg-white rounded-xl border overflow-hidden" style="border-color:var(--line)">
        @for($r = 0; $r < 6; $r++)
        <div class="flex items-center px-5 py-3.5 animate-pulse" style="border-bottom:1px solid var(--line)">
            <div class="h-3.5 rounded w-1/4 mr-8" style="background:var(--line)"></div>
            <div class="h-3.5 rounded w-16 mr-8" style="background:var(--line)"></div>
            <div class="h-3.5 rounded w-1/4 mr-8" style="background:var(--line)"></div>
            <div class="h-3.5 rounded w-24 ml-auto" style="background:var(--line)"></div>
        </div>
        @endfor
    </div>

    {{-- Table --}}
    <div wire:loading.remove wire:target="search,status,date,resetFilters"
         class="bg-white rounded-xl border overflow-hidden" style="border-color:var(--line)">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr style="background:var(--bg-soft); border-bottom:1px solid var(--line)">
                        <th class="text-left px-5 py-3 text-[11px] font-medium uppercase tracking-wider font-mono" style="color:var(--ink-mute)">Invoice</th>
                        <th class="text-left px-4 py-3 text-[11px] font-medium uppercase tracking-wider font-mono" style="color:var(--ink-mute)">Tanggal</th>
                        <th class="text-left px-4 py-3 text-[11px] font-medium uppercase tracking-wider font-mono" style="color:var(--ink-mute)">Kasir</th>
                        <th class="text-right px-4 py-3 text-[11px] font-medium uppercase tracking-wider font-mono" style="color:var(--ink-mute)">Total</th>
                        <th class="text-center px-4 py-3 text-[11px] font-medium uppercase tracking-wider font-mono" style="color:var(--ink-mute)">Status</th>
                        <th class="text-right px-5 py-3 text-[11px] font-medium uppercase tracking-wider font-mono" style="color:var(--ink-mute)">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sales as $sale)
                    <tr class="group" style="border-bottom:1px solid var(--line)" onmouseenter="this.style.background='var(--bg-soft)'" onmouseleave="this.style.background=''">
                        <td class="px-5 py-3.5 font-medium font-mono" style="color:var(--ink)">{{ $sale->invoice_number }}</td>
                        <td class="px-4 py-3.5" style="color:var(--ink-soft)">{{ $sale->sale_date->format('d/m/Y') }}</td>
                        <td class="px-4 py-3.5" style="color:var(--ink-soft)">{{ $sale->creator->name ?? '—' }}</td>
                        <td class="px-4 py-3.5 text-right font-mono font-medium tabular-nums" style="color:var(--ink)">
                            Rp {{ number_format($sale->total_price, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3.5 text-center">
                            @if($sale->status->value === 'approved')
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-medium" style="background:#F0FDF4;color:var(--success)">Approved</span>
                            @elseif($sale->status->value === 'pending')
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-medium" style="background:#FFFBEB;color:#B45309">Pending</span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-medium" style="background:var(--bg-soft);color:var(--ink-mute)">Cancelled</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                <a href="{{ route('sales.show', $sale) }}"
                                   title="Lihat Detail"
                                   class="inline-flex items-center justify-center w-7 h-7 rounded-lg transition-colors"
                                   style="background:var(--bg-soft);color:var(--ink-soft)"
                                   onmouseenter="this.style.background='#E4E9F2'" onmouseleave="this.style.background='var(--bg-soft)'">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                                @if($sale->status->value === 'approved')
                                <a href="{{ route('sales.print', $sale) }}" target="_blank"
                                   title="Cetak Struk"
                                   class="inline-flex items-center justify-center w-7 h-7 rounded-lg transition-colors"
                                   style="background:#F0FDF4;color:var(--success)"
                                   onmouseenter="this.style.background='#DCFCE7'" onmouseleave="this.style.background='#F0FDF4'">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                    </svg>
                                </a>
                                @endif
                                @if(auth()->user()->role->value === 'superadmin')
                                <a href="{{ route('sales.edit', $sale) }}"
                                   title="Edit Transaksi"
                                   class="inline-flex items-center justify-center w-7 h-7 rounded-lg transition-colors"
                                   style="background:#EFF6FF;color:var(--accent)"
                                   onmouseenter="this.style.background='#DBEAFE'" onmouseleave="this.style.background='#EFF6FF'">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                    </svg>
                                </a>
                                <form method="POST" action="{{ route('sales.destroy', $sale) }}" onsubmit="return confirm('Hapus transaksi {{ $sale->invoice_number }}? Stok akan dikembalikan.')">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                            title="Hapus Transaksi"
                                            class="inline-flex items-center justify-center w-7 h-7 rounded-lg transition-colors"
                                            style="background:#FFF5F5;color:var(--warn)"
                                            onmouseenter="this.style.background='#FEE2E2'" onmouseleave="this.style.background='#FFF5F5'">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-5 py-14 text-center text-sm" style="color:var(--ink-mute)">
                            Tidak ada transaksi ditemukan.
                            <a href="{{ route('sales.create') }}" style="color:var(--accent)" class="font-medium hover:underline ml-1">Buat transaksi pertama</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($sales->total() > 0)
        <div class="px-5 py-3 flex items-center justify-between" style="border-top:1px solid var(--line); background:var(--bg-soft)">
            <span class="text-xs font-mono" style="color:var(--ink-mute)">
                Menampilkan {{ $sales->firstItem() ?? 0 }}–{{ $sales->lastItem() ?? 0 }} dari {{ $sales->total() }}
            </span>
            {{ $sales->links() }}
        </div>
        @endif
    </div>
</div>
