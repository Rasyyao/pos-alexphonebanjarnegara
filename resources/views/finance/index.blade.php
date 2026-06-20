1
    {{-- Money Flow Summary --}}
    @php $netProfit = $totalProfit - $totalExpenses; @endphp
    <div class="bg-white rounded-xl border overflow-hidden" style="border-color:var(--line)">
        <div class="px-5 py-3.5 flex items-center justify-between" style="border-bottom:1px solid var(--line)">
            <div>
                <span class="text-sm font-semibold" style="color:var(--ink)">Arus Kas (Money Flow)</span>
                <p class="text-xs mt-0.5" style="color:var(--ink-mute)">Ringkasan pemasukan, laba, dan pengeluaran</p>
            </div>
            <a href="{{ route('finance.export') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium transition-colors"
               style="background:var(--bg-soft);color:var(--ink-soft)"
               onmouseenter="this.style.background='var(--line)'" onmouseleave="this.style.background='var(--bg-soft)'">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Export Data
            </a>
        </div>
        <div class="grid grid-cols-2 lg:grid-cols-4 divide-x divide-y lg:divide-y-0" style="border-color:var(--line)">
            <div class="px-6 py-5" style="border-color:var(--line)">
                <div class="text-[11px] font-medium uppercase tracking-widest font-mono mb-1" style="color:var(--success)">+ Pemasukan</div>
                <div class="text-xl font-semibold font-mono tabular-nums mb-1" style="color:var(--ink)">
                    Rp {{ number_format($totalRevenue, 0, ',', '.') }}
                </div>
                <div class="text-xs" style="color:var(--ink-mute)">Total omzet penjualan</div>
            </div>
            <div class="px-6 py-5" style="border-color:var(--line)">
                <div class="text-[11px] font-medium uppercase tracking-widest font-mono mb-1" style="color:var(--accent)">Laba Kotor</div>
                <div class="text-xl font-semibold font-mono tabular-nums mb-1" style="color:var(--ink)">
                    Rp {{ number_format($totalProfit, 0, ',', '.') }}
                </div>
                <div class="text-xs" style="color:var(--ink-mute)">Omzet − harga beli barang</div>
            </div>
            <div class="px-6 py-5" style="border-color:var(--line)">
                <div class="text-[11px] font-medium uppercase tracking-widest font-mono mb-1" style="color:var(--warn)">− Pengeluaran</div>
                <div class="text-xl font-semibold font-mono tabular-nums mb-1" style="color:var(--ink)">
                    Rp {{ number_format($totalExpenses, 0, ',', '.') }}
                </div>
                <div class="text-xs" style="color:var(--ink-mute)">{{ $totalExpenses > 0 ? number_format(count($expensesByCategory)) . ' kategori pengeluaran' : '0 item pengeluaran' }}</div>
            </div>
            <div class="px-6 py-5" style="border-color:var(--line);{{ $netProfit >= 0 ? 'background:#F0FDF4' : 'background:#FFF7F7' }}">
                <div class="text-[11px] font-medium uppercase tracking-widest font-mono mb-1" style="color:var(--ink-mute)">= Laba Bersih</div>
                <div class="text-xl font-semibold font-mono tabular-nums mb-1" style="{{ $netProfit >= 0 ? 'color:var(--success)' : 'color:var(--warn)' }}">
                    Rp {{ number_format(abs($netProfit), 0, ',', '.') }}
                    {{ $netProfit < 0 ? '(Minus)' : '' }}
                </div>
                <div class="text-xs" style="color:var(--ink-mute)">Laba kotor − pengeluaran</div>
            </div>
        </div>

        {{-- Detail pengeluaran per kategori --}}
        @if($totalExpenses > 0)
        <div class="px-6 py-5" style="border-top:1px solid var(--line);background:var(--bg-soft)">
            <div class="flex items-center justify-between mb-4">
                <span class="text-[11px] font-medium uppercase tracking-widest font-mono" style="color:var(--ink-mute)">Detail Pengeluaran per Kategori</span>
                <span class="text-xs font-mono tabular-nums" style="color:var(--warn)">Total Rp {{ number_format($totalExpenses, 0, ',', '.') }}</span>
            </div>
            <div class="space-y-3">
                @foreach($expensesByCategory->sortDesc() as $cat => $total)
                @php $pct = $totalExpenses > 0 ? round($total / $totalExpenses * 100) : 0; @endphp
                <div>
                    <div class="flex items-center justify-between text-xs mb-1.5">
                        <span class="flex items-center gap-2">
                            <span class="font-medium" style="color:var(--ink)">{{ $cat === 'tarik_owner' ? 'Tarik Saldo Owner' : ($cat === 'listrik' ? 'Listrik & Gas' : ucwords($cat)) }}</span>
                            <span class="font-mono" style="color:var(--ink-mute)">{{ $pct }}%</span>
                        </span>
                        <span class="font-mono tabular-nums font-medium" style="color:var(--warn)">Rp {{ number_format($total, 0, ',', '.') }}</span>
                    </div>
                    <div class="h-1.5 rounded-full overflow-hidden" style="background:#FFFFFF;border:1px solid var(--line)">
                        <div class="h-full rounded-full" style="width:{{ $pct }}%;background:var(--warn)"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    {{-- Main grid --}}
    <div class="grid lg:grid-cols-2 gap-5">

        {{-- Modal / Capital --}}
        <div class="bg-white rounded-xl border overflow-hidden" style="border-color:var(--line)">
            <div class="px-5 py-3.5 flex items-center justify-between" style="border-bottom:1px solid var(--line)">
                <div class="flex items-center gap-3">
                    <h3 class="text-sm font-semibold" style="color:var(--ink)">Modal</h3>
                    <span class="text-sm font-semibold font-mono tabular-nums" style="color:var(--ink-mute)">
                        Rp {{ number_format($totalCapital, 0, ',', '.') }}
                    </span>
                </div>
                <div class="flex items-center gap-2">
                    <button onclick="openModal('modal-kurangi-modal')"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium transition-colors"
                            style="background:#FFF5F5;color:var(--warn)"
                            onmouseenter="this.style.background='#FEE2E2'" onmouseleave="this.style.background='#FFF5F5'">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4"/>
                        </svg>
                        Kurangi Modal
                    </button>
                    <button onclick="openModal('modal-tambah-modal')"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium transition-colors"
                            style="background:#EFF6FF;color:var(--accent)"
                            onmouseenter="this.style.background='#DBEAFE'" onmouseleave="this.style.background='#EFF6FF'">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                        </svg>
                        Tambah Modal
                    </button>
                </div>
            </div>
            <div class="overflow-x-auto">
                @forelse($capitals as $cap)
                <div class="px-5 py-3 flex items-center justify-between text-sm transition-colors"
                     style="border-bottom:1px solid var(--line)"
                     onmouseenter="this.style.background='var(--bg-soft)'" onmouseleave="this.style.background=''">
                    <div>
                        <div class="font-medium" style="color:var(--ink)">{{ $cap->description }}</div>
                        <div class="text-xs font-mono mt-0.5 flex items-center gap-1.5" style="color:var(--ink-mute)">
                            {{ $cap->entry_date->format('d/m/Y') }} ·
                            @if($cap->type === 'withdrawal')
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold uppercase" style="background:#FEF2F2;color:var(--warn)">Pengurangan</span>
                            @elseif($cap->type === 'addition')
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold uppercase" style="background:#EFF6FF;color:var(--accent)">Tambahan</span>
                            @elseif($cap->type === 'initial')
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold uppercase" style="background:#F0FDF4;color:var(--success)">Awal</span>
                            @else
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold uppercase" style="background:var(--bg-soft);color:var(--ink-mute)">{{ $cap->type }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        @if($cap->type === 'withdrawal')
                            <span class="font-medium font-mono tabular-nums" style="color:var(--warn)">− Rp {{ number_format($cap->amount, 0, ',', '.') }}</span>
                        @else
                            <span class="font-medium font-mono tabular-nums" style="color:var(--ink)">Rp {{ number_format($cap->amount, 0, ',', '.') }}</span>
                        @endif
                        <form method="POST" action="{{ route('capitals.destroy', $cap) }}" onsubmit="return confirm('Hapus modal ini?')">
                            @csrf @method('DELETE')
                            <button class="text-xs px-2 py-1 rounded-md transition-colors"
                                    style="background:#FFF5F5;color:var(--warn)"
                                    onmouseenter="this.style.background='#FEE2E2'" onmouseleave="this.style.background='#FFF5F5'">Hapus</button>
                        </form>
                    </div>
                </div>
                @empty
                <p class="px-5 py-8 text-sm text-center" style="color:var(--ink-mute)">Belum ada catatan modal</p>
                @endforelse
            </div>
            @if($capitals->total() > 0)
            <div class="px-5 py-3 flex items-center justify-between" style="border-top:1px solid var(--line);background:var(--bg-soft)">
                <span class="text-xs font-mono" style="color:var(--ink-mute)">{{ $capitals->firstItem() ? $capitals->firstItem().'–'.$capitals->lastItem().' dari ' : '' }}{{ $capitals->total() }} modal</span>
                {{ $capitals->links() }}
            </div>
            @endif
        </div>

        {{-- Pengeluaran --}}
        <div class="bg-white rounded-xl border overflow-hidden" style="border-color:var(--line)">
            <div class="px-5 py-3.5 flex items-center justify-between" style="border-bottom:1px solid var(--line)">
                <div class="flex items-center gap-3">
                    <h3 class="text-sm font-semibold" style="color:var(--ink)">Pengeluaran</h3>
                    <span class="text-sm font-semibold font-mono tabular-nums" style="color:var(--warn)">
                        Rp {{ number_format($totalExpenses, 0, ',', '.') }}
                    </span>
                </div>
                <button onclick="openModal('modal-tambah-pengeluaran')"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium transition-colors"
                        style="background:#FFF5F5;color:var(--warn)"
                        onmouseenter="this.style.background='#FEE2E2'" onmouseleave="this.style.background='#FFF5F5'">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    Tambah Pengeluaran
                </button>
            </div>
            <div class="overflow-x-auto">
                @forelse($expenses as $exp)
                <div class="px-5 py-3 flex items-center justify-between text-sm transition-colors"
                     style="border-bottom:1px solid var(--line)"
                     onmouseenter="this.style.background='var(--bg-soft)'" onmouseleave="this.style.background=''">
                    <div>
                        <div class="font-medium" style="color:var(--ink)">{{ $exp->description }}</div>
                        <div class="text-xs mt-0.5 flex items-center gap-2" style="color:var(--ink-mute)">
                            <span class="font-mono">{{ $exp->expense_date->format('d/m/Y') }}</span>
                            <span class="px-1.5 py-0.5 rounded" style="background:var(--bg-soft)">{{ $exp->category === 'tarik_owner' ? 'Tarik Saldo Owner' : ($exp->category === 'listrik' ? 'Listrik & Gas' : ucwords($exp->category)) }}</span>
                        </div>
                        @if($exp->notes)
                            <div class="text-xs mt-0.5" style="color:var(--ink-mute)">{{ $exp->notes }}</div>
                        @endif
                    </div>
                    <div class="flex items-center gap-3 flex-shrink-0 ml-4">
                        <span class="font-medium font-mono tabular-nums" style="color:var(--warn)">Rp {{ number_format($exp->amount, 0, ',', '.') }}</span>
                        <form method="POST" action="{{ route('expenses.destroy', $exp) }}" onsubmit="return confirm('Hapus pengeluaran ini?')">
                            @csrf @method('DELETE')
                            <button class="text-xs px-2 py-1 rounded-md transition-colors"
                                    style="background:#FFF5F5;color:var(--warn)"
                                    onmouseenter="this.style.background='#FEE2E2'" onmouseleave="this.style.background='#FFF5F5'">Hapus</button>
                        </form>
                    </div>
                </div>
                @empty
                <div class="px-5 py-10 text-center">
                    <p class="text-sm" style="color:var(--ink-mute)">Belum ada catatan pengeluaran</p>
                    <button onclick="openModal('modal-tambah-pengeluaran')"
                            class="mt-2 text-xs font-medium hover:underline" style="color:var(--accent)">
                        Catat pengeluaran pertama
                    </button>
                </div>
                @endforelse
            </div>
            @if($expenses->total() > 0)
            <div class="px-5 py-3 flex items-center justify-between" style="border-top:1px solid var(--line);background:var(--bg-soft)">
                <span class="text-xs font-mono" style="color:var(--ink-mute)">{{ $expenses->firstItem() ? $expenses->firstItem().'–'.$expenses->lastItem().' dari ' : '' }}{{ $expenses->total() }} pengeluaran</span>
                {{ $expenses->links() }}
            </div>
            @endif
        </div>

    </div>

    {{-- Utang Aktif --}}
    <div class="bg-white rounded-xl border overflow-hidden" style="border-color:var(--line)">
        <div class="px-5 py-3.5" style="border-bottom:1px solid var(--line)">
            <h3 class="text-sm font-semibold" style="color:var(--ink)">Utang Aktif</h3>
        </div>
        <livewire:debt-list />
    </div>

    {{-- Mutasi Dana --}}
    <div class="bg-white rounded-xl border overflow-hidden" style="border-color:var(--line)">
        <div class="px-5 py-3.5 flex items-center justify-between" style="border-bottom:1px solid var(--line)">
            <div>
                <h3 class="text-sm font-semibold" style="color:var(--ink)">Mutasi Dana</h3>
                <p class="text-xs mt-0.5" style="color:var(--ink-mute)">Catat perpindahan uang antara Kas dan ATM</p>
            </div>
            <div class="flex items-center gap-3">
                {{-- Summary badges --}}
                <span class="hidden sm:inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-mono" style="background:#f0fdf4;color:#16a34a">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 11l5-5m0 0l5 5m-5-5v12"/></svg>
                    Setor ATM Rp {{ number_format($totalCashToAtm, 0, ',', '.') }}
                </span>
                <span class="hidden sm:inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-mono" style="background:#eff6ff;color:#1d4ed8">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 13l-5 5m0 0l-5-5m5 5V6"/></svg>
                    Tarik Tunai Rp {{ number_format($totalAtmToCash, 0, ',', '.') }}
                </span>
                <button id="btn-catat-mutasi" onclick="openModal('modal-mutasi-dana')"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium transition-colors"
                        style="background:#EFF6FF;color:var(--accent)"
                        onmouseenter="this.style.background='#DBEAFE'" onmouseleave="this.style.background='#EFF6FF'">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                    </svg>
                    Catat Mutasi
                </button>
            </div>
        </div>
        <div class="overflow-x-auto">
            @forelse($fundTransfers as $ft)
            <div class="px-5 py-3 flex items-center justify-between text-sm transition-colors"
                 style="border-bottom:1px solid var(--line)"
                 onmouseenter="this.style.background='var(--bg-soft)'" onmouseleave="this.style.background=''">
                <div class="flex items-center gap-3">
                    @if($ft->direction === 'cash_to_atm')
                        <span class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0" style="background:#f0fdf4;color:#16a34a">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 11l5-5m0 0l5 5m-5-5v12"/></svg>
                        </span>
                    @else
                        <span class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0" style="background:#eff6ff;color:#1d4ed8">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 13l-5 5m0 0l-5-5m5 5V6"/></svg>
                        </span>
                    @endif
                    <div>
                        <div class="font-medium" style="color:var(--ink)">
                            @if($ft->direction === 'cash_to_atm')
                                <span style="color:#16a34a">Kas → ATM</span>
                            @else
                                <span style="color:#1d4ed8">ATM → Kas</span>
                            @endif
                        </div>
                        <div class="text-xs font-mono mt-0.5 flex items-center gap-1.5" style="color:var(--ink-mute)">
                            {{ $ft->transfer_date->format('d/m/Y') }}
                            @if($ft->description)
                                · {{ $ft->description }}
                            @endif
                            · oleh {{ $ft->creator->name ?? '-' }}
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-3 flex-shrink-0 ml-4">
                    <span class="font-medium font-mono tabular-nums" style="color:var(--ink)">Rp {{ number_format($ft->amount, 0, ',', '.') }}</span>
                    <form method="POST" action="{{ route('fund-transfers.destroy', $ft) }}" onsubmit="return confirm('Hapus catatan mutasi ini?')">
                        @csrf @method('DELETE')
                        <button class="text-xs px-2 py-1 rounded-md transition-colors"
                                style="background:#FFF5F5;color:var(--warn)"
                                onmouseenter="this.style.background='#FEE2E2'" onmouseleave="this.style.background='#FFF5F5'">Hapus</button>
                    </form>
                </div>
            </div>
            @empty
            <div class="px-5 py-10 text-center">
                <div class="w-12 h-12 rounded-xl mx-auto mb-3 flex items-center justify-center" style="background:var(--bg-soft)">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" style="color:var(--ink-mute)">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                    </svg>
                </div>
                <p class="text-sm" style="color:var(--ink-mute)">Belum ada catatan mutasi dana</p>
                <button onclick="openModal('modal-mutasi-dana')" class="mt-2 text-xs font-medium hover:underline" style="color:var(--accent)">
                    Catat mutasi pertama
                </button>
            </div>
            @endforelse
        </div>
        @if($fundTransfers->total() > 0)
        <div class="px-5 py-3 flex items-center justify-between" style="border-top:1px solid var(--line);background:var(--bg-soft)">
            <span class="text-xs font-mono" style="color:var(--ink-mute)">{{ $fundTransfers->firstItem() ? $fundTransfers->firstItem().'–'.$fundTransfers->lastItem().' dari ' : '' }}{{ $fundTransfers->total() }} mutasi</span>
            {{ $fundTransfers->links() }}
        </div>
        @endif
    </div>

</div>

{{-- ========== MODAL: Tambah Modal ========== --}}
<div id="modal-tambah-modal" class="fixed inset-0 z-[100] hidden overflow-y-auto" onclick="closeModalOutside(event, 'modal-tambah-modal')">
    <div class="fixed inset-0" style="background:rgba(10,37,64,.5)"></div>
    <div class="relative min-h-full flex items-start justify-center px-4 pt-12 pb-12">
        <div class="w-full max-w-lg bg-white rounded-2xl shadow-2xl overflow-hidden modal-pop" onclick="event.stopPropagation()">
            <div class="flex items-start justify-between px-6 py-5" style="border-bottom:1px solid var(--line)">
                <div class="flex items-center gap-3">
                    <span class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0" style="background:#EFF6FF;color:var(--accent)">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                    </span>
                    <div>
                        <h3 class="text-base font-semibold leading-none" style="color:var(--ink)">Tambah Modal</h3>
                        <p class="text-xs mt-1.5" style="color:var(--ink-mute)">Catat modal yang masuk ke usaha</p>
                    </div>
                </div>
                <button onclick="closeModal('modal-tambah-modal')" class="w-8 h-8 flex items-center justify-center rounded-lg transition-colors" style="color:var(--ink-mute);background:var(--bg-soft)" onmouseenter="this.style.background='var(--line)'" onmouseleave="this.style.background='var(--bg-soft)'">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form method="POST" action="{{ route('capitals.store') }}" class="p-6 space-y-5">
                @csrf
                <div>
                    <label class="field-label">Keterangan <span style="color:var(--warn)">*</span></label>
                    <input type="text" name="description" placeholder="mis. Modal awal, tambahan modal..." required class="field-input" />
                </div>
                <div>
                    <label class="field-label">Jumlah <span style="color:var(--warn)">*</span></label>
                    <div class="money-wrap">
                        <span class="rp-prefix">Rp</span>
                        <input type="text" name="amount" id="cap-amount" required placeholder="0"
                               class="field-input money-input" inputmode="numeric" style="height:48px;font-size:16px" />
                    </div>
                    <div class="flex flex-wrap gap-2 mt-2.5">
                        @foreach([500000=>'500rb', 1000000=>'1jt', 5000000=>'5jt', 10000000=>'10jt'] as $v => $lbl)
                        <button type="button" onclick="setAmount('cap-amount',{{ $v }})"
                                class="px-3 py-1.5 rounded-lg text-xs font-medium font-mono transition-colors"
                                style="background:var(--bg-soft);color:var(--ink-soft)"
                                onmouseenter="this.style.background='var(--line)'" onmouseleave="this.style.background='var(--bg-soft)'">{{ $lbl }}</button>
                        @endforeach
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="field-label">Jenis</label>
                        <select name="type" required class="field-input">
                            <option value="initial">Modal Awal</option>
                            <option value="addition">Tambahan</option>
                            <option value="purchase">Pembelian</option>
                        </select>
                    </div>
                    <div>
                        <label class="field-label">Tanggal</label>
                        <input type="date" name="entry_date" value="{{ today()->toDateString() }}" required class="field-input" />
                    </div>
                </div>
                <div class="flex gap-3 pt-1">
                    <button type="submit" class="btn-primary flex-1">Simpan Modal</button>
                    <button type="button" onclick="closeModal('modal-tambah-modal')" class="btn-secondary" style="padding:0 24px">Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ========== MODAL: Kurangi Modal ========== --}}
<div id="modal-kurangi-modal" class="fixed inset-0 z-[100] hidden overflow-y-auto" onclick="closeModalOutside(event, 'modal-kurangi-modal')">
    <div class="fixed inset-0" style="background:rgba(10,37,64,.5)"></div>
    <div class="relative min-h-full flex items-start justify-center px-4 pt-12 pb-12">
        <div class="w-full max-w-lg bg-white rounded-2xl shadow-2xl overflow-hidden modal-pop" onclick="event.stopPropagation()">
            <div class="flex items-start justify-between px-6 py-5" style="border-bottom:1px solid var(--line)">
                <div class="flex items-center gap-3">
                    <span class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0" style="background:#FFF5F5;color:var(--warn)">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4"/></svg>
                    </span>
                    <div>
                        <h3 class="text-base font-semibold leading-none" style="color:var(--ink)">Kurangi Modal</h3>
                        <p class="text-xs mt-1.5" style="color:var(--ink-mute)">Catat penarikan atau pengurangan modal usaha</p>
                    </div>
                </div>
                <button onclick="closeModal('modal-kurangi-modal')" class="w-8 h-8 flex items-center justify-center rounded-lg transition-colors" style="color:var(--ink-mute);background:var(--bg-soft)" onmouseenter="this.style.background='var(--line)'" onmouseleave="this.style.background='var(--bg-soft)'">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form method="POST" action="{{ route('capitals.store') }}" class="p-6 space-y-5">
                @csrf
                <input type="hidden" name="type" value="withdrawal" />
                <div>
                    <label class="field-label">Keterangan <span style="color:var(--warn)">*</span></label>
                    <input type="text" name="description" placeholder="mis. Penarikan pemilik, pengambilan modal..." required class="field-input" />
                </div>
                <div>
                    <label class="field-label">Jumlah yang Dikurangi <span style="color:var(--warn)">*</span></label>
                    <div class="money-wrap">
                        <span class="rp-prefix">Rp</span>
                        <input type="text" name="amount" id="withdraw-amount" required placeholder="0"
                               class="field-input money-input" inputmode="numeric" style="height:48px;font-size:16px" />
                    </div>
                    <div class="flex flex-wrap gap-2 mt-2.5">
                        @foreach([500000=>'500rb', 1000000=>'1jt', 5000000=>'5jt', 10000000=>'10jt'] as $v => $lbl)
                        <button type="button" onclick="setAmount('withdraw-amount',{{ $v }})"
                                class="px-3 py-1.5 rounded-lg text-xs font-medium font-mono transition-colors"
                                style="background:var(--bg-soft);color:var(--ink-soft)"
                                onmouseenter="this.style.background='var(--line)'" onmouseleave="this.style.background='var(--bg-soft)'">{{ $lbl }}</button>
                        @endforeach
                    </div>
                </div>
                <div>
                    <label class="field-label">Tanggal</label>
                    <input type="date" name="entry_date" value="{{ today()->toDateString() }}" required class="field-input" />
                </div>
                <div class="flex gap-3 pt-1">
                    <button type="submit" class="flex-1 inline-flex items-center justify-center gap-2 font-semibold rounded-xl transition-colors"
                            style="height:44px;font-size:14px;background:var(--warn);color:#fff"
                            onmouseenter="this.style.background='#dc2626'" onmouseleave="this.style.background='var(--warn)'">
                        Kurangi Modal
                    </button>
                    <button type="button" onclick="closeModal('modal-kurangi-modal')" class="btn-secondary" style="padding:0 24px">Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ========== MODAL: Tambah Pengeluaran ========== --}}
<div id="modal-tambah-pengeluaran" class="fixed inset-0 z-[100] hidden overflow-y-auto" onclick="closeModalOutside(event, 'modal-tambah-pengeluaran')">
    <div class="fixed inset-0" style="background:rgba(10,37,64,.5)"></div>
    <div class="relative min-h-full flex items-start justify-center px-4 pt-12 pb-12">
        <div class="w-full max-w-lg bg-white rounded-2xl shadow-2xl overflow-hidden modal-pop" onclick="event.stopPropagation()">
            <div class="flex items-start justify-between px-6 py-5" style="border-bottom:1px solid var(--line)">
                <div class="flex items-center gap-3">
                    <span class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0" style="background:#FFF5F5;color:var(--warn)">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                    </span>
                    <div>
                        <h3 class="text-base font-semibold leading-none" style="color:var(--ink)">Tambah Pengeluaran</h3>
                        <p class="text-xs mt-1.5" style="color:var(--ink-mute)">Catat biaya operasional toko</p>
                    </div>
                </div>
                <button onclick="closeModal('modal-tambah-pengeluaran')" class="w-8 h-8 flex items-center justify-center rounded-lg transition-colors" style="color:var(--ink-mute);background:var(--bg-soft)" onmouseenter="this.style.background='var(--line)'" onmouseleave="this.style.background='var(--bg-soft)'">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form method="POST" action="{{ route('expenses.store') }}" class="p-6 space-y-5">
                @csrf
                <div>
                    <label class="field-label">Keterangan <span style="color:var(--warn)">*</span></label>
                    <input type="text" name="description" placeholder="mis. Bayar listrik, gaji karyawan..." required class="field-input" />
                </div>
                <div>
                    <label class="field-label">Jumlah <span style="color:var(--warn)">*</span></label>
                    <div class="money-wrap">
                        <span class="rp-prefix">Rp</span>
                        <input type="text" name="amount" id="exp-amount" required placeholder="0"
                               class="field-input money-input" inputmode="numeric" style="height:48px;font-size:16px" />
                    </div>
                    <div class="flex flex-wrap gap-2 mt-2.5">
                        @foreach([50000=>'50rb', 100000=>'100rb', 500000=>'500rb', 1000000=>'1jt'] as $v => $lbl)
                        <button type="button" onclick="setAmount('exp-amount',{{ $v }})"
                                class="px-3 py-1.5 rounded-lg text-xs font-medium font-mono transition-colors"
                                style="background:var(--bg-soft);color:var(--ink-soft)"
                                onmouseenter="this.style.background='var(--line)'" onmouseleave="this.style.background='var(--bg-soft)'">{{ $lbl }}</button>
                        @endforeach
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="field-label">Kategori</label>
                        <select name="category" required class="field-input">
                            <option value="operasional">Operasional</option>
                            <option value="gaji">Gaji</option>
                            <option value="sewa">Sewa</option>
                            <option value="listrik">Listrik</option>
                            <option value="lainnya">Lainnya</option>
                            @if (auth()->user()->role->value === 'superadmin')
                                <option value="tarik_owner">Tarik Saldo Owner</option>
                            @endif
                        </select>
                    </div>
                    <div>
                        <label class="field-label">Tanggal</label>
                        <input type="date" name="expense_date" value="{{ today()->toDateString() }}" required class="field-input" />
                    </div>
                </div>
                <div>
                    <label class="field-label">Catatan</label>
                    <textarea name="notes" rows="2" class="field-input" placeholder="Opsional, detail tambahan..."></textarea>
                </div>
                <div class="flex gap-3 pt-1">
                    <button type="submit" class="btn-primary flex-1" style="background:var(--warn)">Simpan Pengeluaran</button>
                    <button type="button" onclick="closeModal('modal-tambah-pengeluaran')" class="btn-secondary" style="padding:0 24px">Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ========== MODAL: Mutasi Dana ========== --}}
<div id="modal-mutasi-dana" class="fixed inset-0 z-[100] hidden overflow-y-auto" onclick="closeModalOutside(event, 'modal-mutasi-dana')">
    <div class="fixed inset-0" style="background:rgba(10,37,64,.5)"></div>
    <div class="relative min-h-full flex items-start justify-center px-4 pt-12 pb-12">
        <div class="w-full max-w-lg bg-white rounded-2xl shadow-2xl overflow-hidden modal-pop" onclick="event.stopPropagation()">
            <div class="flex items-start justify-between px-6 py-5" style="border-bottom:1px solid var(--line)">
                <div class="flex items-center gap-3">
                    <span class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0" style="background:#eff6ff;color:var(--accent)">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                        </svg>
                    </span>
                    <div>
                        <h3 class="text-base font-semibold leading-none" style="color:var(--ink)">Catat Mutasi Dana</h3>
                        <p class="text-xs mt-1.5" style="color:var(--ink-mute)">Pindahkan uang antara Kas (tunai) dan ATM (transfer)</p>
                    </div>
                </div>
                <button onclick="closeModal('modal-mutasi-dana')" class="w-8 h-8 flex items-center justify-center rounded-lg transition-colors" style="color:var(--ink-mute);background:var(--bg-soft)" onmouseenter="this.style.background='var(--line)'" onmouseleave="this.style.background='var(--bg-soft)'">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form method="POST" action="{{ route('fund-transfers.store') }}" class="p-6 space-y-5">
                @csrf

                {{-- Direction selector --}}
                <div>
                    <label class="field-label">Arah Mutasi <span style="color:var(--warn)">*</span></label>
                    <div class="grid grid-cols-2 gap-3 mt-1">
                        <label id="lbl-cash-to-atm"
                               class="mutasi-option flex flex-col items-center gap-2 p-4 rounded-xl border-2 cursor-pointer transition-all"
                               style="border-color:var(--line)">
                            <input type="radio" name="direction" value="cash_to_atm" class="sr-only" onchange="selectMutasiDir(this)" required>
                            <span class="w-9 h-9 rounded-lg flex items-center justify-center" style="background:#f0fdf4;color:#16a34a">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 11l5-5m0 0l5 5m-5-5v12"/>
                                </svg>
                            </span>
                            <div class="text-center">
                                <div class="text-xs font-semibold" style="color:var(--ink)">Kas → ATM</div>
                                <div class="text-[10px] mt-0.5" style="color:var(--ink-mute)">Setor tunai ke ATM</div>
                            </div>
                        </label>
                        <label id="lbl-atm-to-cash"
                               class="mutasi-option flex flex-col items-center gap-2 p-4 rounded-xl border-2 cursor-pointer transition-all"
                               style="border-color:var(--line)">
                            <input type="radio" name="direction" value="atm_to_cash" class="sr-only" onchange="selectMutasiDir(this)" required>
                            <span class="w-9 h-9 rounded-lg flex items-center justify-center" style="background:#eff6ff;color:#1d4ed8">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 13l-5 5m0 0l-5-5m5 5V6"/>
                                </svg>
                            </span>
                            <div class="text-center">
                                <div class="text-xs font-semibold" style="color:var(--ink)">ATM → Kas</div>
                                <div class="text-[10px] mt-0.5" style="color:var(--ink-mute)">Tarik tunai dari ATM</div>
                            </div>
                        </label>
                    </div>
                </div>

                {{-- Amount --}}
                <div>
                    <label class="field-label">Jumlah <span style="color:var(--warn)">*</span></label>
                    <div class="money-wrap">
                        <span class="rp-prefix">Rp</span>
                        <input type="text" name="amount" id="mutasi-amount" required placeholder="0"
                               class="field-input money-input" inputmode="numeric" style="height:48px;font-size:16px" />
                    </div>
                    <div class="flex flex-wrap gap-2 mt-2.5">
                        @foreach([500000=>'500rb', 1000000=>'1jt', 2000000=>'2jt', 5000000=>'5jt'] as $v => $lbl)
                        <button type="button" onclick="setAmount('mutasi-amount',{{ $v }})"
                                class="px-3 py-1.5 rounded-lg text-xs font-medium font-mono transition-colors"
                                style="background:var(--bg-soft);color:var(--ink-soft)"
                                onmouseenter="this.style.background='var(--line)'" onmouseleave="this.style.background='var(--bg-soft)'">{{ $lbl }}</button>
                        @endforeach
                    </div>
                </div>

                {{-- Description + Date --}}
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2 sm:col-span-1">
                        <label class="field-label">Keterangan</label>
                        <input type="text" name="description" placeholder="mis. Setor modal ke rekening..." class="field-input" />
                    </div>
                    <div class="col-span-2 sm:col-span-1">
                        <label class="field-label">Tanggal <span style="color:var(--warn)">*</span></label>
                        <input type="date" name="transfer_date" value="{{ today()->toDateString() }}" required class="field-input" />
                    </div>
                </div>

                <div class="flex gap-3 pt-1">
                    <button type="submit" id="btn-submit-mutasi" class="btn-primary flex-1">Simpan Mutasi</button>
                    <button type="button" onclick="closeModal('modal-mutasi-dana')" class="btn-secondary" style="padding:0 24px">Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>

@include('components.money-format')

<style>
@keyframes modalPop { from { opacity:0; transform:translateY(-12px) scale(.98); } to { opacity:1; transform:translateY(0) scale(1); } }
.modal-pop { animation: modalPop 200ms cubic-bezier(.16,1,.3,1); }
</style>
<script>
function openModal(id) {
    const el = document.getElementById(id);
    el.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    const first = el.querySelector('input[name="description"]');
    if (first) setTimeout(() => first.focus(), 50);
}
function closeModal(id) {
    document.getElementById(id).classList.add('hidden');
    document.body.style.overflow = '';
}
function closeModalOutside(e, id) {
    if (e.target === document.getElementById(id)) closeModal(id);
}
function setAmount(id, val) {
    const el = document.getElementById(id);
    if (el) { el.value = val.toLocaleString('id-ID'); }
}
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        ['modal-tambah-modal','modal-kurangi-modal','modal-tambah-pengeluaran','modal-mutasi-dana'].forEach(closeModal);
    }
});

function selectMutasiDir(radio) {
    document.querySelectorAll('.mutasi-option').forEach(el => {
        el.style.borderColor = 'var(--line)';
        el.style.background  = '';
    });
    const lbl = radio.closest('label');
    if (radio.value === 'cash_to_atm') {
        lbl.style.borderColor = '#16a34a';
        lbl.style.background  = '#f0fdf4';
    } else {
        lbl.style.borderColor = '#1d4ed8';
        lbl.style.background  = '#eff6ff';
    }
}
</script>
@endsection
