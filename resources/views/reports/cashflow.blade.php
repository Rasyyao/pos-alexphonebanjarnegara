@extends('layouts.app')
@section('title', 'Arus Kas (Cashflow)')

@section('content')
    <div class="space-y-6">

        {{-- Header --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold" style="color:var(--ink)">Arus Kas (Cashflow)</h2>
                <p class="text-xs mt-0.5" style="color:var(--ink-mute)">Pantau ringkasan arus kas masuk, pengeluaran operasional, dan pengelolaan modal usaha</p>
            </div>
        </div>

        {{-- Period Filter Bar --}}
        <div class="bg-white rounded-xl border p-4 shadow-sm" style="border-color:var(--line)">
            <form method="GET" action="{{ route('reports.cashflow') }}" id="date-filter-form"
                class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <input type="hidden" name="preset" id="active-preset" value="{{ request('preset', 'all') }}" />

                <div class="flex flex-wrap items-center gap-4">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold" style="color:var(--ink-soft)">Periode:</span>
                        <div class="flex items-center gap-2 px-3 rounded-lg border bg-[#F8FAFC]"
                            style="border-color:var(--line); height: 36px;">
                            <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}"
                                class="text-xs focus:outline-none bg-transparent"
                                style="border:none!important;outline:none!important;box-shadow:none!important;padding:0!important;background:transparent;color:var(--ink);width:115px;" />
                            <span class="text-xs" style="color:var(--ink-mute)">s/d</span>
                            <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}"
                                class="text-xs focus:outline-none bg-transparent"
                                style="border:none!important;outline:none!important;box-shadow:none!important;padding:0!important;background:transparent;color:var(--ink);width:115px;" />
                        </div>
                    </div>
                    <div class="flex items-center gap-1 bg-gray-100 p-1 rounded-lg text-xs font-semibold" style="height:36px;">
                        @foreach (['today' => 'Hari Ini', 'week' => 'Minggu Ini', 'month' => 'Bulan Ini', 'all' => 'Semua'] as $p => $lbl)
                            @php $isActive = request('preset', 'all') === $p; @endphp
                            <button type="button" onclick="setPreset('{{ $p }}')"
                                class="px-3 rounded-md transition-all text-[11px] h-7 flex items-center justify-center {{ $isActive ? 'bg-white text-blue-600 font-bold shadow-sm' : 'text-gray-500 hover:text-gray-800' }}">
                                {{ $lbl }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <div class="flex items-center gap-2 shrink-0 self-end md:self-auto">
                    <button type="submit"
                        class="text-xs h-9 px-4 font-semibold rounded-lg transition-colors flex items-center gap-1.5 shadow-sm"
                        style="background:var(--accent);color:#fff" onmouseenter="this.style.filter='brightness(0.95)'"
                        onmouseleave="this.style.filter='none'">
                        Filter Periode
                    </button>
                    @if (auth()->user()->role->value === 'superadmin')
                        <a href="{{ route('reports.export', ['type' => 'finance', 'start_date' => request('start_date'), 'end_date' => request('end_date')]) }}"
                            class="text-xs h-9 px-4 font-semibold rounded-lg transition-all flex items-center gap-1.5 border shadow-sm"
                            style="background:#F0FDF4;color:var(--success);border-color:#BBF7D0"
                            onmouseenter="this.style.background='#DCFCE7'" onmouseleave="this.style.background='#F0FDF4'">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Export Finansial (.xlsx)
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Summary Metric Cards --}}
        @php $netCash = $cashflow['net']; @endphp
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">

            {{-- Modal Disetor --}}
            <div class="bg-white rounded-xl border p-4 shadow-sm" style="border-color:var(--line)">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center mb-3" style="background:rgba(59,130,246,0.08)">
                    <svg class="w-4 h-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z" />
                    </svg>
                </div>
                <p class="text-[10px] font-bold uppercase tracking-widest font-mono" style="color:var(--ink-mute)">Modal Disetor</p>
                <p class="text-lg font-bold font-mono tabular-nums mt-0.5" style="color:var(--ink)">
                    Rp {{ number_format($modalAwal, 0, ',', '.') }}
                </p>
                <p class="text-[10px] mt-1" style="color:var(--ink-mute)">Total modal masuk (Awal & Tambahan)</p>
            </div>

            {{-- Kas Liquid --}}
            <div class="bg-white rounded-xl border p-4 shadow-sm" style="border-color:var(--line)">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center mb-3" style="background:rgba(16,128,107,0.08)">
                    <svg class="w-4 h-4" style="color:var(--success)" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <p class="text-[10px] font-bold uppercase tracking-widest font-mono" style="color:var(--ink-mute)">Kas Liquid</p>
                <p class="text-lg font-bold font-mono tabular-nums mt-0.5" style="{{ $modalSekarang >= 0 ? 'color:var(--success)' : 'color:var(--warn)' }}">
                    {{ $modalSekarang < 0 ? '−' : '' }} Rp {{ number_format(abs($modalSekarang), 0, ',', '.') }}
                </p>
                <p class="text-[10px] mt-1" style="color:var(--ink-mute)">Modal + Omzet − Beli HP − Biaya</p>
            </div>

            {{-- Aset Stok HP --}}
            <div class="bg-white rounded-xl border p-4 shadow-sm" style="border-color:var(--line)">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center mb-3" style="background:rgba(124,58,237,0.08)">
                    <svg class="w-4 h-4 text-violet-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18" />
                    </svg>
                </div>
                <p class="text-[10px] font-bold uppercase tracking-widest font-mono text-violet-500">Aset Stok HP</p>
                <p class="text-lg font-bold font-mono tabular-nums mt-0.5 text-violet-600">
                    Rp {{ number_format($assetValue ?? 0, 0, ',', '.') }}
                </p>
                <p class="text-[10px] mt-1" style="color:var(--ink-mute)">Nilai beli HP ready di stok</p>
            </div>

            {{-- Piutang Aktif --}}
            <div class="bg-white rounded-xl border p-4 shadow-sm" style="border-color:var(--line)">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center mb-3 bg-amber-50">
                    <svg class="w-4 h-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <p class="text-[10px] font-bold uppercase tracking-widest font-mono text-amber-600">Piutang Aktif</p>
                <p class="text-lg font-bold font-mono tabular-nums mt-0.5" style="color:var(--ink)">
                    Rp {{ number_format($unpaidDebts, 0, ',', '.') }}
                </p>
                <p class="text-[10px] mt-1" style="color:var(--ink-mute)">Total piutang belum tertagih</p>
            </div>

        </div>

        {{-- Main Content: Cashflow + Modal --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Arus Kas (Cashflow Summary) --}}
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl border overflow-hidden shadow-sm h-full" style="border-color:var(--line)">
                    <div class="px-5 py-4 border-b" style="border-color:var(--line)">
                        <h3 class="text-sm font-semibold" style="color:var(--ink)">Arus Kas Periode Ini</h3>
                        <p class="text-[11px] mt-0.5" style="color:var(--ink-mute)">Pemasukan, pengeluaran & net cashflow</p>
                    </div>
                    <div class="p-5 space-y-3">

                        {{-- Inflow --}}
                        <div class="flex items-center justify-between p-3 rounded-lg" style="background:rgba(16,128,107,0.05)">
                            <div>
                                <div class="text-[10px] font-bold uppercase tracking-widest font-mono" style="color:var(--success)">+ Pemasukan (Omzet)</div>
                                <div class="text-base font-bold font-mono tabular-nums mt-0.5" style="color:var(--ink)">
                                    Rp {{ number_format($cashflow['inflow'], 0, ',', '.') }}
                                </div>
                            </div>
                            <svg class="w-7 h-7 opacity-15" style="color:var(--success)" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                            </svg>
                        </div>

                        {{-- Outflow --}}
                        <div class="flex items-center justify-between p-3 rounded-lg" style="background:rgba(194,65,12,0.05)">
                            <div>
                                <div class="text-[10px] font-bold uppercase tracking-widest font-mono" style="color:var(--warn)">− Pengeluaran</div>
                                <div class="text-base font-bold font-mono tabular-nums mt-0.5" style="color:var(--ink)">
                                    Rp {{ number_format($cashflow['outflow'], 0, ',', '.') }}
                                </div>
                            </div>
                            <svg class="w-7 h-7 opacity-15" style="color:var(--warn)" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" />
                            </svg>
                        </div>

                        {{-- Net Cashflow --}}
                        <div class="flex items-center justify-between p-3 rounded-lg border"
                            style="{{ $netCash >= 0 ? 'background:rgba(16,128,107,0.07);border-color:rgba(16,128,107,0.2)' : 'background:rgba(194,65,12,0.07);border-color:rgba(194,65,12,0.2)' }}">
                            <div>
                                <div class="text-[10px] font-bold uppercase tracking-widest font-mono" style="color:var(--ink-soft)">= Net Cashflow</div>
                                <div class="text-xl font-bold font-mono tabular-nums mt-0.5"
                                    style="{{ $netCash >= 0 ? 'color:var(--success)' : 'color:var(--warn)' }}">
                                    {{ $netCash < 0 ? '−' : '' }} Rp {{ number_format(abs($netCash), 0, ',', '.') }}
                                </div>
                            </div>
                            <span class="text-xl font-bold" style="{{ $netCash >= 0 ? 'color:var(--success)' : 'color:var(--warn)' }}">
                                {{ $netCash >= 0 ? '▲' : '▼' }}
                            </span>
                        </div>

                        {{-- Total Aset Breakdown --}}
                        <div class="pt-2 border-t" style="border-color:var(--line)">
                            <p class="text-[10px] font-bold uppercase tracking-widest font-mono mb-2" style="color:var(--ink-mute)">Komposisi Total Aset</p>
                            <div class="space-y-1.5">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="flex items-center gap-1.5" style="color:var(--ink-soft)">
                                        <span class="inline-block w-2 h-2 rounded-full" style="background:var(--success)"></span>
                                        Kas Liquid
                                    </span>
                                    <span class="font-mono font-semibold tabular-nums" style="color:var(--ink)">
                                        Rp {{ number_format($modalSekarang, 0, ',', '.') }}
                                    </span>
                                </div>
                                <div class="flex items-center justify-between text-xs">
                                    <span class="flex items-center gap-1.5" style="color:var(--ink-soft)">
                                        <span class="inline-block w-2 h-2 rounded-full bg-violet-400"></span>
                                        Aset Stok HP
                                    </span>
                                    <span class="font-mono font-semibold tabular-nums" style="color:var(--ink)">
                                        Rp {{ number_format($assetValue ?? 0, 0, ',', '.') }}
                                    </span>
                                </div>
                                <div class="flex items-center justify-between text-xs pt-1.5 border-t font-bold" style="border-color:var(--line);color:var(--ink)">
                                    <span>Total Aset</span>
                                    <span class="font-mono tabular-nums">
                                        Rp {{ number_format(($assetValue ?? 0) + $modalSekarang, 0, ',', '.') }}
                                    </span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            {{-- Modal Usaha (Capitals) --}}
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl border overflow-hidden shadow-sm" style="border-color:var(--line)">
                    <div class="px-5 py-4 border-b flex items-center justify-between" style="border-color:var(--line)">
                        <div>
                            <h3 class="text-sm font-semibold" style="color:var(--ink)">Modal Usaha (Capitals)</h3>
                            <p class="text-[11px] mt-0.5" style="color:var(--ink-mute)">Riwayat penyetoran modal dan status kas aktif</p>
                        </div>
                        @if(auth()->user()->role->value === 'superadmin')
                        <button onclick="openModal('modal-tambah-modal')"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium transition-colors"
                                style="background:#EFF6FF;color:var(--accent)"
                                onmouseenter="this.style.background='#DBEAFE'" onmouseleave="this.style.background='#EFF6FF'">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                            </svg>
                            Tambah Modal
                        </button>
                        @endif
                    </div>

                    {{-- Modal Awal vs Modal Sekarang --}}
                    <div class="grid grid-cols-2 divide-x" style="border-bottom:1px solid var(--line)">
                        <div class="p-4 bg-gray-50/60">
                            <p class="text-[9px] font-bold uppercase tracking-widest font-sans mb-1" style="color:var(--ink-mute)">Modal Disetor (Lifetime)</p>
                            <p class="text-lg font-bold font-mono tabular-nums" style="color:var(--ink)">
                                Rp {{ number_format($modalAwal, 0, ',', '.') }}
                            </p>
                            <p class="text-[10px] font-sans mt-0.5" style="color:var(--ink-mute)">Total modal masuk (Awal & Tambahan)</p>
                        </div>
                        <div class="p-4" style="{{ $modalSekarang >= 0 ? 'background:rgba(16,128,107,0.03)' : 'background:rgba(194,65,12,0.03)' }}">
                            <p class="text-[9px] font-bold uppercase tracking-widest font-sans mb-1" style="color:var(--ink-mute)">Kas Liquid Sekarang</p>
                            <p class="text-lg font-bold font-mono tabular-nums" style="{{ $modalSekarang >= 0 ? 'color:var(--success)' : 'color:var(--warn)' }}">
                                {{ $modalSekarang < 0 ? '−' : '' }} Rp {{ number_format(abs($modalSekarang), 0, ',', '.') }}
                            </p>
                            <p class="text-[10px] font-sans mt-0.5" style="color:var(--ink-mute)">Modal + Omzet − Beli HP − Biaya</p>
                        </div>
                    </div>

                    {{-- Capitals Log Table --}}
                    <div class="overflow-x-auto" style="max-height:240px;overflow-y:auto;">
                        <table class="w-full text-xs">
                            <thead class="sticky top-0" style="z-index:1">
                                <tr style="background:var(--bg-soft);border-bottom:1px solid var(--line)">
                                    <th class="text-left px-5 py-2.5 font-bold uppercase tracking-wider font-mono whitespace-nowrap" style="color:var(--ink-mute)">Tanggal</th>
                                    <th class="text-left px-5 py-2.5 font-bold uppercase tracking-wider font-mono" style="color:var(--ink-mute)">Keterangan</th>
                                    <th class="text-left px-5 py-2.5 font-bold uppercase tracking-wider font-mono" style="color:var(--ink-mute)">Jenis</th>
                                    <th class="text-right px-5 py-2.5 font-bold uppercase tracking-wider font-mono" style="color:var(--ink-mute)">Jumlah</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($capitals as $capital)
                                    <tr class="hover:bg-gray-50/60 transition-colors" style="border-bottom:1px solid var(--line)">
                                        <td class="px-5 py-2.5 font-mono whitespace-nowrap" style="color:var(--ink-soft)">
                                            {{ $capital->entry_date->format('d/m/Y') }}
                                        </td>
                                        <td class="px-5 py-2.5 font-medium" style="color:var(--ink)">
                                            {{ $capital->description }}
                                        </td>
                                        <td class="px-5 py-2.5">
                                            @if ($capital->type === 'initial')
                                                <span class="px-2 py-0.5 rounded-full bg-blue-50 text-blue-700 font-semibold font-mono text-[9px]">Awal</span>
                                            @else
                                                <span class="px-2 py-0.5 rounded-full bg-green-50 text-green-700 font-semibold font-mono text-[9px]">Tambahan</span>
                                            @endif
                                        </td>
                                        <td class="px-5 py-2.5 text-right font-mono font-bold tabular-nums" style="color:var(--ink)">
                                            Rp {{ number_format($capital->amount, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-5 py-8 text-center text-xs" style="color:var(--ink-mute)">Belum ada data modal disetor</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($capitals->total() > 0)
                        <div class="px-5 py-3 flex items-center justify-between" style="border-top:1px solid var(--line);background:var(--bg-soft)">
                            <span class="text-xs font-mono" style="color:var(--ink-mute)">{{ $capitals->total() }} modal</span>
                            {{ $capitals->links() }}
                        </div>
                    @endif
                </div>
            </div>

        </div>

    </div>

    {{-- ========== MODAL: Tambah Modal ========== --}}
    @if(auth()->user()->role->value === 'superadmin')
    <div id="modal-tambah-modal" class="fixed inset-0 z-[100] hidden" onclick="closeModalOutside(event, 'modal-tambah-modal')">
        <div class="fixed inset-0" style="background:rgba(10,37,64,.5)"></div>
        <div class="fixed inset-0 flex items-center justify-center p-4 overflow-y-auto">
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
    @include('components.money-format')
    <style>
    @keyframes modalPop { from { opacity:0; transform:translateY(-12px) scale(.98); } to { opacity:1; transform:translateY(0) scale(1); } }
    .modal-pop { animation: modalPop 200ms cubic-bezier(.16,1,.3,1); }
    </style>
    @endif

    <style>
        #date-filter-form input[type="date"] {
            border: none !important;
            outline: none !important;
            box-shadow: none !important;
            background: transparent !important;
            padding: 0 !important;
            -webkit-appearance: none;
            appearance: none;
        }
    </style>

    <script>
        function openModal(id) {
            const el = document.getElementById(id);
            if (!el) return;
            el.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            const first = el.querySelector('input[name="description"]');
            if (first) setTimeout(() => first.focus(), 50);
        }
        function closeModal(id) {
            const el = document.getElementById(id);
            if (el) el.classList.add('hidden');
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
            if (e.key === 'Escape') closeModal('modal-tambah-modal');
        });

        function setPreset(preset) {
            const startInput = document.getElementById('start_date');
            const endInput = document.getElementById('end_date');
            const presetInput = document.getElementById('active-preset');
            const today = new Date();

            const formatDate = (date) => {
                const yyyy = date.getFullYear();
                let mm = date.getMonth() + 1;
                let dd = date.getDate();
                if (dd < 10) dd = '0' + dd;
                if (mm < 10) mm = '0' + mm;
                return yyyy + '-' + mm + '-' + dd;
            };

            presetInput.value = preset;

            if (preset === 'today') {
                startInput.value = formatDate(today);
                endInput.value = formatDate(today);
            } else if (preset === 'week') {
                const day = today.getDay();
                const diff = today.getDate() - day + (day === 0 ? -6 : 1);
                const monday = new Date(today.setDate(diff));
                startInput.value = formatDate(monday);
                endInput.value = formatDate(new Date());
            } else if (preset === 'month') {
                const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
                startInput.value = formatDate(firstDay);
                endInput.value = formatDate(new Date());
            } else if (preset === 'all') {
                startInput.value = '';
                endInput.value = '';
            }

            document.getElementById('date-filter-form').submit();
        }
    </script>
@endsection
