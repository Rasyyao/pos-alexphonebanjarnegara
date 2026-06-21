<div>
    {{-- Filter bar --}}
    <div class="bg-white rounded-xl border p-4 mb-4 flex flex-wrap gap-2" style="border-color:var(--line)">
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari aksesoris..." class="field-input" style="width:200px;height:36px;padding:0 10px;font-size:13px" />

        <select wire:model.live="category" class="field-input" style="width:auto;height:36px;padding:0 10px;font-size:13px;min-width:140px">
            <option value="">Semua Kategori</option>
            @foreach($categories as $cat)
                <option value="{{ $cat }}">{{ $cat }}</option>
            @endforeach
        </select>

        <select wire:model.live="stock_status" class="field-input" style="width:auto;height:36px;padding:0 10px;font-size:13px;min-width:130px">
            <option value="">Semua Status</option>
            <option value="ready">Ready (Stok > 0)</option>
            <option value="empty">Habis (Stok = 0)</option>
        </select>

        <button wire:click="resetFilters" class="btn-secondary" style="height:36px;padding:0 14px;font-size:13px">Reset</button>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl border overflow-hidden relative transition-opacity duration-200" 
         style="border-color:var(--line)"
         wire:loading.class="opacity-60 pointer-events-none">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr style="background:var(--bg-soft); border-bottom:1px solid var(--line)">
                        <th class="text-left px-4 py-3 text-[11px] font-medium uppercase tracking-wider font-mono" style="color:var(--ink-mute)">Nama</th>
                        <th class="text-left px-4 py-3 text-[11px] font-medium uppercase tracking-wider font-mono" style="color:var(--ink-mute)">Kategori</th>
                        <th class="text-right px-4 py-3 text-[11px] font-medium uppercase tracking-wider font-mono" style="color:var(--ink-mute)">Stok</th>
                        <th class="text-right px-4 py-3 text-[11px] font-medium uppercase tracking-wider font-mono" style="color:var(--ink-mute)">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($accessories as $acc)
                    <tr wire:key="accessory-{{ $acc->id }}" class="group transition-colors" style="border-bottom:1px solid var(--line)"
                        onmouseenter="this.style.background='var(--bg-soft)'" onmouseleave="this.style.background=''">
                        <td class="px-4 py-3.5 font-medium" style="color:var(--ink)">
                            <a href="{{ route('accessories.show', $acc) }}" class="hover:underline" style="color:var(--ink)">{{ $acc->name }}</a>
                        </td>
                        <td class="px-4 py-3.5 text-xs" style="color:var(--ink-mute)">{{ $acc->category ?? '—' }}</td>
                        <td class="px-4 py-3.5 text-right font-mono font-medium tabular-nums"
                            style="color:{{ $acc->stock_qty <= 3 ? 'var(--warn)' : 'var(--ink)' }}">{{ $acc->stock_qty }}</td>
                        <!-- <td class="px-4 py-3.5 text-right font-mono tabular-nums" style="color:var(--ink-soft)">Rp {{ number_format($acc->purchase_price, 0, ',', '.') }}</td> -->
                        <td class="px-4 py-3.5">
                            <div class="flex items-center justify-end gap-1.5">
                                <a href="{{ route('accessories.show', $acc) }}"
                                   title="Lihat Detail"
                                   class="inline-flex items-center justify-center w-7 h-7 rounded-lg transition-colors"
                                   style="background:var(--bg-soft);color:var(--ink-soft)"
                                   onmouseenter="this.style.background='#E4E9F2'" onmouseleave="this.style.background='var(--bg-soft)'">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                                <a href="{{ route('accessories.edit', $acc) }}"
                                   title="Edit"
                                   class="inline-flex items-center justify-center w-7 h-7 rounded-lg transition-colors"
                                   style="background:#EFF6FF;color:var(--accent)"
                                   onmouseenter="this.style.background='#DBEAFE'" onmouseleave="this.style.background='#EFF6FF'">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                    </svg>
                                </a>
                                <form method="POST" action="{{ route('accessories.destroy', $acc) }}" onsubmit="return confirm('Hapus aksesoris ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                            title="Hapus"
                                            class="inline-flex items-center justify-center w-7 h-7 rounded-lg transition-colors"
                                            style="background:#FFF5F5;color:var(--warn)"
                                            onmouseenter="this.style.background='#FEE2E2'" onmouseleave="this.style.background='#FFF5F5'">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-4 py-14 text-center text-sm" style="color:var(--ink-mute)">
                            Tidak ada aksesoris ditemukan.
                            <a href="{{ route('accessories.create') }}" class="font-medium hover:underline ml-1" style="color:var(--accent)">Tambah sekarang</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($accessories->total() > 0)
        <div class="px-5 py-3 flex items-center justify-between" style="border-top:1px solid var(--line);background:var(--bg-soft)">
            <span class="text-xs font-mono" style="color:var(--ink-mute)">Menampilkan {{ $accessories->firstItem() ?? 0 }}–{{ $accessories->lastItem() ?? 0 }} dari {{ $accessories->total() }}</span>
            {{ $accessories->links() }}
        </div>
        @endif
    </div>
</div>
