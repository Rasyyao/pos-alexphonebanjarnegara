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
