@extends('layouts.app')
@section('title', 'Keuangan')

@section('content')
<div class="space-y-5">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-5">
        <div>
            <h2 class="text-xl font-bold" style="color:var(--ink)">Keuangan</h2>
            <p class="text-xs mt-0.5" style="color:var(--ink-mute)">Kelola modal, pengeluaran, utang, dan laporan finansial</p>
        </div>
    </div>

    {{-- KPI --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl border p-5 card-lift" style="border-color:var(--line)">
            <div class="text-[11px] font-medium uppercase tracking-widest font-mono mb-2" style="color:var(--ink-mute)">Total Omzet</div>
            <div class="text-2xl font-semibold font-mono tabular-nums" style="color:var(--ink)">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</div>
        </div>
        <div class="bg-white rounded-xl border p-5 card-lift" style="border-color:var(--line)">
            <div class="text-[11px] font-medium uppercase tracking-widest font-mono mb-2" style="color:var(--ink-mute)">Total Laba</div>
            <div class="text-2xl font-semibold font-mono tabular-nums" style="color:var(--success)">Rp {{ number_format($totalProfit, 0, ',', '.') }}</div>
        </div>
        <div class="bg-white rounded-xl border p-5 card-lift" style="border-color:var(--line)">
            <div class="text-[11px] font-medium uppercase tracking-widest font-mono mb-2" style="color:var(--ink-mute)">Utang Belum Lunas</div>
            <div class="text-2xl font-semibold font-mono tabular-nums" style="color:var(--warn)">Rp {{ number_format($pendingDebts, 0, ',', '.') }}</div>
        </div>
        <div class="bg-white rounded-xl border p-5 card-lift" style="border-color:var(--line)">
            <div class="text-[11px] font-medium uppercase tracking-widest font-mono mb-2" style="color:var(--ink-mute)">Nilai Aset Stok</div>
            <div class="text-2xl font-semibold font-mono tabular-nums" style="color:var(--ink)">Rp {{ number_format($assetValue, 0, ',', '.') }}</div>
        </div>
    </div>

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
                            <span class="font-medium capitalize" style="color:var(--ink)">{{ $cat }}</span>
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
            <div class="overflow-x-auto">
                @forelse($capitals as $cap)
                <div class="px-5 py-3 flex items-center justify-between text-sm transition-colors"
                     style="border-bottom:1px solid var(--line)"
                     onmouseenter="this.style.background='var(--bg-soft)'" onmouseleave="this.style.background=''">
                    <div>
                        <div class="font-medium" style="color:var(--ink)">{{ $cap->description }}</div>
                        <div class="text-xs font-mono mt-0.5" style="color:var(--ink-mute)">
                            {{ $cap->entry_date->format('d/m/Y') }} ·
                            <span class="capitalize">{{ $cap->type }}</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="font-medium font-mono tabular-nums" style="color:var(--ink)">Rp {{ number_format($cap->amount, 0, ',', '.') }}</span>
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
                            <span class="px-1.5 py-0.5 rounded capitalize" style="background:var(--bg-soft)">{{ $exp->category }}</span>
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
        ['modal-tambah-modal','modal-tambah-pengeluaran'].forEach(closeModal);
    }
});
</script>
@endsection
