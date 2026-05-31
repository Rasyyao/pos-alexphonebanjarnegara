<div>
    <div class="px-4 py-3 flex gap-1.5" style="border-bottom:1px solid var(--line)">
        @foreach(['unpaid' => 'Belum Lunas', 'partial' => 'Cicilan', 'paid' => 'Lunas', 'all' => 'Semua'] as $val => $label)
        <button wire:click="$set('filter', '{{ $val }}')"
                class="px-3 py-1 rounded-full text-xs font-medium transition-colors"
                style="{{ $filter === $val ? 'background:var(--accent);color:#fff' : 'background:var(--bg-soft);color:var(--ink-soft)' }}"
                onmouseenter="{{ $filter === $val ? '' : "this.style.background='var(--line)'" }}"
                onmouseleave="{{ $filter === $val ? '' : "this.style.background='var(--bg-soft)'" }}">
            {{ $label }}
        </button>
        @endforeach
    </div>

    <div>
        @forelse($debts as $debt)
        <div class="px-5 py-3.5 flex items-center justify-between text-sm transition-colors"
             style="border-bottom:1px solid var(--line)"
             onmouseenter="this.style.background='var(--bg-soft)'" onmouseleave="this.style.background=''">
            <div>
                <div class="font-medium font-mono" style="color:var(--ink)">{{ $debt->sale->invoice_number ?? '—' }}</div>
                <div class="text-xs mt-0.5" style="color:var(--ink-mute)">{{ $debt->sale->creator->name ?? '—' }} · {{ $debt->created_at?->format('d/m/Y') }}</div>
            </div>
            <div class="flex items-center gap-3">
                <span class="font-medium font-mono tabular-nums" style="color:var(--warn)">Rp {{ number_format($debt->amount, 0, ',', '.') }}</span>
                @if($debt->status !== 'paid')
                <button wire:click="markPaid({{ $debt->id }})" wire:confirm="Tandai utang ini lunas?"
                        class="px-2.5 py-1 rounded-full text-xs font-medium transition-colors"
                        style="background:var(--bg-soft);color:var(--success);border:1px solid var(--line)"
                        onmouseenter="this.style.background='#F0FDF4'" onmouseleave="this.style.background='var(--bg-soft)'">
                    Lunas
                </button>
                @else
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-medium" style="background:var(--bg-soft);color:var(--ink-mute)">Lunas</span>
                @endif
            </div>
        </div>
        @empty
        <p class="px-5 py-10 text-sm text-center" style="color:var(--ink-mute)">Tidak ada utang</p>
        @endforelse
    </div>
    @if($debts->total() > 0)
    <div class="px-5 py-3 flex items-center justify-between" style="border-top:1px solid var(--line);background:var(--bg-soft)">
        <span class="text-xs font-mono" style="color:var(--ink-mute)">
            {{ $debts->firstItem() ?? 0 }}–{{ $debts->lastItem() ?? 0 }} dari {{ $debts->total() }} utang
        </span>
        {{ $debts->links() }}
    </div>
    @endif
</div>
