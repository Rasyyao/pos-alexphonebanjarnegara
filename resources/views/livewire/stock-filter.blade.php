<div>
    {{-- Filter bar --}}
    <div class="bg-white rounded-xl border p-4 mb-4 flex flex-wrap gap-2" style="border-color:var(--line)">
        <select wire:model.live="brand_id" class="field-input" style="width:auto;height:36px;padding:0 10px;font-size:13px;min-width:120px">
            <option value="">Semua Brand</option>
            @foreach($brands as $brand)
                <option value="{{ $brand->id }}">{{ $brand->name }}</option>
            @endforeach
        </select>

        <select wire:model.live="model_id" class="field-input" style="width:auto;height:36px;padding:0 10px;font-size:13px;min-width:120px;{{ !$brand_id ? 'opacity:.5;cursor:not-allowed' : '' }}" @if(!$brand_id) disabled @endif>
            <option value="">Semua Model</option>
            @foreach($models as $model)
                <option value="{{ $model->id }}">{{ $model->name }}</option>
            @endforeach
        </select>

        <select wire:model.live="unit_type" class="field-input" style="width:auto;height:36px;padding:0 10px;font-size:13px">
            <option value="">Semua Tipe</option>
            <option value="baru">Baru</option>
            <option value="second">Second</option>
        </select>

        <select wire:model.live="status" class="field-input" style="width:auto;height:36px;padding:0 10px;font-size:13px">
            <option value="">Semua Status</option>
            <option value="ready">Ready</option>
            <option value="sold">Terjual</option>
            <option value="returned">Retur</option>
        </select>

        <select wire:model.live="grade" class="field-input" style="width:auto;height:36px;padding:0 10px;font-size:13px">
            <option value="">Semua Grade</option>
            <option value="A">Grade A</option>
            <option value="B">Grade B</option>
            <option value="C">Grade C</option>
            <option value="D">Grade D</option>
            <option value="E">Grade E</option>
        </select>

        <input wire:model.live.debounce.300ms="ram" type="text" placeholder="RAM" class="field-input" style="width:90px;height:36px;padding:0 10px;font-size:13px" />
        <input wire:model.live.debounce.300ms="rom" type="text" placeholder="ROM" class="field-input" style="width:90px;height:36px;padding:0 10px;font-size:13px" />

        <button wire:click="resetFilters" class="btn-secondary" style="height:36px;padding:0 14px;font-size:13px">Reset</button>
    </div>

    {{-- Loading skeleton --}}
    <div wire:loading wire:target="brand_id,model_id,unit_type,status,grade,ram,rom,resetFilters"
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
    <div wire:loading.remove wire:target="brand_id,model_id,unit_type,status,grade,ram,rom,resetFilters"
         class="bg-white rounded-xl border overflow-hidden" style="border-color:var(--line)">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr style="background:var(--bg-soft); border-bottom:1px solid var(--line)">
                        <th class="text-left px-5 py-3 text-[11px] font-medium uppercase tracking-wider font-mono" style="color:var(--ink-mute)">Unit</th>
                        <th class="text-left px-4 py-3 text-[11px] font-medium uppercase tracking-wider font-mono" style="color:var(--ink-mute)">Tipe</th>
                        <th class="text-left px-4 py-3 text-[11px] font-medium uppercase tracking-wider font-mono" style="color:var(--ink-mute)">Spesifikasi</th>
                        <th class="text-right px-4 py-3 text-[11px] font-medium uppercase tracking-wider font-mono" style="color:var(--ink-mute)">Harga Beli</th>
                        <th class="text-right px-4 py-3 text-[11px] font-medium uppercase tracking-wider font-mono" style="color:var(--ink-mute)">Harga Jual</th>
                        <th class="text-center px-4 py-3 text-[11px] font-medium uppercase tracking-wider font-mono" style="color:var(--ink-mute)">Status</th>
                        <th class="w-40 px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($units as $unit)
                    <tr class="transition-colors" style="border-bottom:1px solid var(--line)"
                        onmouseenter="this.style.background='var(--bg-soft)'" onmouseleave="this.style.background=''">
                        <td class="px-5 py-3.5">
                            <div class="font-semibold" style="color:var(--ink)">{{ $unit->model->brand->name ?? '—' }}</div>
                            <div class="text-xs font-mono mt-0.5" style="color:var(--ink-mute)">{{ $unit->model->name ?? '—' }}</div>
                        </td>
                        <td class="px-4 py-3.5">
                            <span class="px-2 py-0.5 rounded-full text-[11px] font-medium"
                                  style="{{ $unit->unit_type->value === 'baru' ? 'background:#EFF6FF;color:var(--accent)' : 'background:#FFFBEB;color:#B45309' }}">
                                {{ ucfirst($unit->unit_type->value) }}
                            </span>
                            @if($unit->grade)
                            <div class="mt-1">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wide"
                                      style="{{ $unit->grade === 'A' ? 'background:var(--ink);color:#fff' : 'background:#92400E;color:#fff' }}">
                                    Grade {{ $unit->grade }}
                                </span>
                            </div>
                            @endif
                        </td>
                        <td class="px-4 py-3.5 text-xs font-mono" style="color:var(--ink-soft)">
                            {{ $unit->ram }}/{{ $unit->rom }}<br>
                            <span style="color:var(--ink-mute)">{{ $unit->color }}</span>
                        </td>
                        <td class="px-4 py-3.5 text-right font-semibold font-mono tabular-nums" style="color:var(--ink)">
                            Rp {{ number_format($unit->purchase_price, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3.5 text-right font-semibold font-mono tabular-nums" style="color:{{ $unit->selling_price ? 'var(--success)' : 'var(--ink-mute)' }}">
                            {{ $unit->selling_price ? 'Rp ' . number_format($unit->selling_price, 0, ',', '.') : '—' }}
                        </td>
                        <td class="px-4 py-3.5 text-center">
                            @if($unit->status->value === 'ready')
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-medium" style="background:#F0FDF4;color:var(--success)">Ready</span>
                            @elseif($unit->status->value === 'sold')
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-medium" style="background:var(--bg-soft);color:var(--ink-mute)">Terjual</span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-medium" style="background:#FFF7F7;color:var(--warn)">Retur</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center justify-end gap-1.5">
                                <a href="{{ route('units.show', $unit) }}"
                                   title="Lihat Detail"
                                   class="inline-flex items-center justify-center w-7 h-7 rounded-lg transition-colors"
                                   style="background:var(--bg-soft);color:var(--ink-soft)"
                                   onmouseenter="this.style.background='#E4E9F2'" onmouseleave="this.style.background='var(--bg-soft)'">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </a>
                                <a href="{{ route('units.edit', $unit) }}"
                                   title="Edit"
                                   class="inline-flex items-center justify-center w-7 h-7 rounded-lg transition-colors"
                                   style="background:#EFF6FF;color:var(--accent)"
                                   onmouseenter="this.style.background='#DBEAFE'" onmouseleave="this.style.background='#EFF6FF'">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                    </svg>
                                </a>
                                <form method="POST" action="{{ route('units.destroy', $unit) }}" onsubmit="return confirm('Hapus unit ini?')">
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
                        <td colspan="7" class="px-5 py-14 text-center text-sm" style="color:var(--ink-mute)">
                            Tidak ada unit ditemukan.
                            <a href="{{ route('units.create') }}" style="color:var(--accent)" class="font-medium hover:underline ml-1">Tambah unit pertama</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($units->total() > 0)
        <div class="px-5 py-3 flex items-center justify-between" style="border-top:1px solid var(--line); background:var(--bg-soft)">
            <span class="text-xs font-mono" style="color:var(--ink-mute)">
                Menampilkan {{ $units->firstItem() ?? 0 }}–{{ $units->lastItem() ?? 0 }} dari {{ $units->total() }}
            </span>
            {{ $units->links() }}
        </div>
        @endif
    </div>
</div>
