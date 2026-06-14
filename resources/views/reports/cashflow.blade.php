@extends('layouts.app')
@section('title', 'Arus Kas (Cashflow)')

@section('content')
    <div class="space-y-6">

        {{-- Header --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold" style="color:var(--ink)">Arus Kas (Cashflow)</h2>
                <p class="text-xs mt-0.5" style="color:var(--ink-mute)">Pantau ringkasan arus kas masuk, pengeluaran
                    operasional, dan pengelolaan modal usaha</p>
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
                    <div class="flex items-center gap-1 bg-gray-100 p-1 rounded-lg text-xs font-semibold"
                        style="height:36px;">
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
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                stroke-width="2.2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Export Finansial (.xlsx)
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Active Period Banner --}}
        @php
            $periodLabel = match (request('preset', 'all')) {
                'today' => 'Hari Ini — ' . now()->isoFormat('D MMM Y'),
                'week' => 'Minggu Ini',
                'month' => 'Bulan Ini — ' . now()->isoFormat('MMMM Y'),
                default => request('start_date') && request('end_date')
                    ? request('start_date') . ' s/d ' . request('end_date')
                    : 'Semua Periode',
            };
        @endphp
        <div class="flex flex-wrap items-center justify-between gap-3 px-1">
            <p class="text-sm font-semibold" style="color:var(--ink)">Ringkasan Keuangan</p>
            <div class="flex items-center gap-2">
                <span class="text-[10px] font-bold uppercase tracking-widest font-mono"
                    style="color:var(--ink-mute)">Periode aktif:</span>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold font-mono"
                    style="background:#EFF6FF;color:var(--accent);border:1px solid #BFDBFE">
                    <svg class="w-3 h-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    {{ $periodLabel }}
                </span>
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold font-mono"
                    style="background:#F0FDF4;color:var(--success);border:1px solid #BBF7D0">
                    <span class="w-1.5 h-1.5 rounded-full bg-current inline-block"></span> ASET = Lifetime
                </span>
            </div>
        </div>

        {{-- Summary Metric Cards — ordered by financial relevance, consistent with dashboard --}}
        @php
            $netCash = $cashflow['net'];
            $totalAset = ($assetValue ?? 0) + ($accAssetValue ?? 0) + $modalSekarang;
        @endphp
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-3">

            {{-- 1. Nominal Aset --}}
            <div class="bg-white rounded-xl border p-5 card-lift" style="border-color:var(--line)">
                <div class="flex items-start justify-between mb-3">
                    <div class="text-[11px] font-medium uppercase tracking-widest font-mono" style="color:var(--ink-mute)">Nominal Aset</div>
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0"
                        style="background:rgba(16,128,107,0.08)">
                        <svg class="w-4 h-4" style="color:var(--success)" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                    </div>
                </div>
                <div class="text-2xl font-semibold leading-none mb-1 font-mono tabular-nums text-emerald-600" style="color:var(--success)">
                    Rp {{ number_format($totalAset, 0, ',', '.') }}
                </div>
                <div class="text-xs" style="color:var(--ink-mute)">modal awal + laba − pengeluaran</div>
            </div>

            {{-- 2. Nominal Modal --}}
            <div class="bg-white rounded-xl border p-5 card-lift" style="border-color:var(--line)">
                <div class="flex items-start justify-between mb-3">
                    <div class="text-[11px] font-medium uppercase tracking-widest font-mono" style="color:var(--ink-mute)">Nominal Modal</div>
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0"
                        style="background:rgba(37,99,235,0.08)">
                        <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                @php $nominalModalVal = $saldoAtmLifetime + $saldoKas - $unpaidDebts; @endphp
                <div class="text-2xl font-semibold leading-none mb-1 font-mono tabular-nums text-blue-600"
                    style="{{ $nominalModalVal >= 0 ? 'color:#2563EB' : 'color:var(--warn)' }}">
                    {{ $nominalModalVal < 0 ? '−' : '' }}Rp {{ number_format(abs($nominalModalVal), 0, ',', '.') }}
                </div>
                <div class="text-xs" style="color:var(--ink-mute)">saldo atm + cash − piutang aktif</div>
            </div>

            {{-- 3. Modal Disetor --}}
            <div class="bg-white rounded-xl border p-5 card-lift" style="border-color:var(--line)">
                <div class="flex items-start justify-between mb-3">
                    <div class="text-[11px] font-medium uppercase tracking-widest font-mono" style="color:var(--ink-mute)">Modal Disetor</div>
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0"
                        style="background:rgba(99,102,241,0.08)">
                        <svg class="w-4 h-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                        </svg>
                    </div>
                </div>
                <div class="text-2xl font-semibold leading-none mb-1 font-mono tabular-nums text-indigo-600" style="color:#6366F1">
                    Rp {{ number_format($modalAwalNonSales, 0, ',', '.') }}
                </div>
                <div class="text-xs" style="color:var(--ink-mute)">Total modal masuk (lifetime)</div>
            </div>

            {{-- 4. Pengeluaran --}}
            <div class="bg-white rounded-xl border p-5 card-lift" style="border-color:var(--line)">
                <div class="flex items-start justify-between mb-3">
                    <div class="text-[11px] font-medium uppercase tracking-widest font-mono" style="color:var(--ink-mute)">Pengeluaran</div>
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0"
                        style="background:rgba(194,65,12,0.08)">
                        <svg class="w-4 h-4" style="color:var(--warn)" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" />
                        </svg>
                    </div>
                </div>
                <div class="text-2xl font-semibold leading-none mb-1 font-mono tabular-nums text-orange-600" style="color:var(--warn)">
                    Rp {{ number_format($cashflow['outflow'], 0, ',', '.') }}
                </div>
                <div class="text-xs" style="color:var(--ink-mute)">Total pengeluaran periode ini</div>
            </div>

            {{-- 5. Piutang Aktif --}}
            <div class="bg-white rounded-xl border p-5 card-lift" style="border-color:var(--line)">
                <div class="flex items-start justify-between mb-3">
                    <div class="text-[11px] font-medium uppercase tracking-widest font-mono" style="color:var(--ink-mute)">Piutang Aktif</div>
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center bg-amber-50 flex-shrink-0">
                        <svg class="w-4 h-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                </div>
                <div class="text-2xl font-semibold leading-none mb-1 font-mono tabular-nums text-amber-600" style="color:#F59E0B">
                    Rp {{ number_format($unpaidDebts, 0, ',', '.') }}
                </div>
                <div class="text-xs" style="color:var(--ink-mute)">Belum tertagih dari pembeli</div>
            </div>

        </div>

        {{-- ===== CAPITAL DISTRIBUTION TRACKER ===== --}}
        @php
            // Total Modal = Modal Disetor (all-time) + Total Omzet (revenue)
            $totalModal = $modalAwalNonSales + $lifetimeRevenue;

            // Where the money currently sits
            $distHP = max(0, (float) ($assetValue ?? 0)); // Stok HP (ready units, at cost)
            $distAcc = max(0, (float) ($accAssetValue ?? 0)); // Stok Aksesoris (at cost × qty)
            $distPiu = max(0, (float) $unpaidDebts); // Piutang Aktif
            $distAtm = max(0, (float) $saldoAtmLifetime); // Saldo ATM = modal_transfer + rev_transfer − hp_transfer
            $distKas = max(0, (float) $saldoKas); // Kas Tunai = modal_cash − withdrawals + rev_cash − hp_cash − acc − expenses

            $distTotal = max(1, $distHP + $distAcc + $distPiu + $distAtm + $distKas);

            $pctHP = round(($distHP / $distTotal) * 100, 1);
            $pctAcc = round(($distAcc / $distTotal) * 100, 1);
            $pctPiu = round(($distPiu / $distTotal) * 100, 1);
            $pctAtm = round(($distAtm / $distTotal) * 100, 1);
            $pctKas = max(0, round(100 - $pctHP - $pctAcc - $pctPiu - $pctAtm, 1));

            // Penerimaan Dana breakdown (how revenue was received)
            $penerimaan = [
                [
                    'label' => 'Tunai (Cash)',
                    'value' => max(0, (float) $saldoKas),
                    'color' => '#10806B',
                    'bg' => 'rgba(16,128,107,0.07)',
                ],
                [
                    'label' => 'Transfer (ATM)',
                    'value' => max(0, (float) $saldoAtmLifetime),
                    'color' => '#2563EB',
                    'bg' => 'rgba(37,99,235,0.07)',
                ],
                [
                    'label' => 'Piutang',
                    'value' => max(0, (float) $unpaidDebts),
                    'color' => '#F59E0B',
                    'bg' => 'rgba(245,158,11,0.07)',
                ],
            ];
            $penTotal = max(1, collect($penerimaan)->sum('value'));

            $lifetimeExpenses = (float) \App\Models\Expense::sum('amount');
            $saldoRill = $totalModal - $lifetimeExpenses;

            $distCategories = [
                [
                    'label' => 'Stok HP',
                    'value' => $distHP,
                    'pct' => $pctHP,
                    'color' => '#7C3AED',
                    'bg' => 'rgba(124,58,237,0.07)',
                ],
                [
                    'label' => 'Stok Aksesoris',
                    'value' => $distAcc,
                    'pct' => $pctAcc,
                    'color' => '#0891B2',
                    'bg' => 'rgba(8,145,178,0.07)',
                ],
                [
                    'label' => 'Piutang Aktif',
                    'value' => $distPiu,
                    'pct' => $pctPiu,
                    'color' => '#F59E0B',
                    'bg' => 'rgba(245,158,11,0.07)',
                ],
                [
                    'label' => 'Saldo ATM',
                    'value' => $distAtm,
                    'pct' => $pctAtm,
                    'color' => '#2563EB',
                    'bg' => 'rgba(37,99,235,0.07)',
                ],
                [
                    'label' => 'Uang Cash',
                    'value' => $distKas,
                    'pct' => $pctKas,
                    'color' => '#10806B',
                    'bg' => 'rgba(16,128,107,0.07)',
                ],
            ];
        @endphp

        <div class="bg-white rounded-xl border shadow-sm overflow-hidden" style="border-color:var(--line)">

            {{-- Tracker Header --}}
            <div class="px-6 py-4 border-b flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3"
                style="border-color:var(--line);background:var(--bg-soft)">
                <div>
                    <h3 class="text-sm font-bold" style="color:var(--ink)">Persebaran Modal Usaha</h3>
                    <p class="text-[11px] mt-0.5" style="color:var(--ink-mute)">Distribusi modal ke seluruh kategori aset
                        bisnis · data lifetime</p>
                </div>
            </div>

            <div class="p-6 space-y-6">

                {{-- Distribution Category Cards --}}
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
                    @foreach ($distCategories as $cat)
                        <div class="rounded-xl border p-3.5 flex flex-col gap-2"
                            style="border-color:var(--line);background:{{ $cat['bg'] }}">
                            <div class="text-[9px] font-bold uppercase tracking-widest font-mono"
                                style="color:{{ $cat['color'] }}">{{ $cat['label'] }}</div>
                            <div class="text-sm font-bold font-mono tabular-nums leading-tight" style="color:var(--ink)">
                                Rp {{ number_format($cat['value'], 0, ',', '.') }}
                            </div>
                            <div class="mt-auto">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-[10px] font-bold font-mono"
                                        style="color:{{ $cat['color'] }}">{{ $cat['pct'] }}%</span>
                                </div>
                                <div class="h-1.5 rounded-full overflow-hidden" style="background:rgba(0,0,0,0.08)">
                                    <div
                                        style="height:100%;width:{{ $cat['pct'] }}%;background:{{ $cat['color'] }};transition:width 0.4s ease">
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Bottom row: Donut + Penerimaan Dana + Saldo Rill --}}
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 pt-2">

                    {{-- Donut Chart --}}
                    <div class="flex flex-col items-center justify-center gap-4">
                        <div class="relative flex-shrink-0" style="width:160px;height:160px">
                            <canvas id="capital-dist-donut"></canvas>
                            <div
                                style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);text-align:center;pointer-events:none">
                                <div class="text-[9px] font-mono font-bold" style="color:var(--ink-mute)">Aset</div>
                                <div class="text-[10px] font-bold font-mono leading-tight" style="color:var(--ink)">
                                    Rp {{ number_format($distTotal, 0, ',', '.') }}
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-x-4 gap-y-1 w-full">
                            @foreach ($distCategories as $cat)
                                <div class="flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full flex-shrink-0"
                                        style="background:{{ $cat['color'] }}"></span>
                                    <span class="text-[9px] font-mono truncate"
                                        style="color:var(--ink-soft)">{{ $cat['label'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Penerimaan Dana --}}
                    <div class="border rounded-xl overflow-hidden" style="border-color:var(--line)">
                        <div class="px-4 py-3 border-b flex items-center gap-2"
                            style="border-color:var(--line);background:var(--bg-soft)">
                            <svg class="w-3.5 h-3.5 flex-shrink-0" style="color:var(--success)" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                            </svg>
                            <div>
                                <div class="text-[10px] font-bold uppercase tracking-widest font-mono"
                                    style="color:var(--ink-mute)">Penerimaan Dana</div>
                                <div class="text-[9px]" style="color:var(--ink-mute)">Komposisi omzet masuk (lifetime)
                                </div>
                            </div>
                        </div>
                        <div class="p-4 space-y-3">
                            @foreach ($penerimaan as $p)
                                @php $pPct = round(($p['value'] / $penTotal) * 100, 1); @endphp
                                <div class="flex flex-col gap-1">
                                    <div class="flex items-center justify-between">
                                        <span class="text-[10px] font-bold font-mono"
                                            style="color:{{ $p['color'] }}">{{ $p['label'] }}</span>
                                        <span class="text-[10px] font-mono tabular-nums" style="color:var(--ink-soft)">
                                            Rp {{ number_format($p['value'], 0, ',', '.') }}
                                            <span class="font-bold" style="color:{{ $p['color'] }}"> ·
                                                {{ $pPct }}%</span>
                                        </span>
                                    </div>
                                    <div class="h-1.5 rounded-full overflow-hidden" style="background:rgba(0,0,0,0.07)">
                                        <div
                                            style="height:100%;width:{{ $pPct }}%;background:{{ $p['color'] }}">
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                            <div class="border-t pt-3 mt-2" style="border-color:var(--line)">
                                <div class="flex items-center justify-between">
                                    <span class="text-[10px] font-bold font-mono uppercase tracking-wider"
                                        style="color:var(--ink-soft)">Total Omzet</span>
                                    <span class="text-xs font-bold font-mono tabular-nums" style="color:var(--success)">
                                        Rp {{ number_format($lifetimeRevenue, 0, ',', '.') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Saldo Rill --}}
                    <div class="border rounded-xl overflow-hidden" style="border-color:var(--line)">
                        <div class="px-4 py-3 border-b flex items-center gap-2"
                            style="border-color:var(--line);background:var(--bg-soft)">
                            <svg class="w-3.5 h-3.5 flex-shrink-0" style="color:var(--ink-mute)" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                            <div>
                                <div class="text-[10px] font-bold uppercase tracking-widest font-mono"
                                    style="color:var(--ink-mute)">Saldo Rill</div>
                                <div class="text-[9px]" style="color:var(--ink-mute)">Net modal setelah pengeluaran</div>
                            </div>
                        </div>
                        <div class="p-4 space-y-2.5">
                            <div class="flex items-center justify-between text-xs py-1.5 border-b"
                                style="border-color:var(--line)">
                                <span style="color:var(--ink-soft)">Modal Disetor</span>
                                <span class="font-bold font-mono text-blue-600">+ Rp
                                    {{ number_format($modalAwalNonSales, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex items-center justify-between text-xs py-1.5 border-b"
                                style="border-color:var(--line)">
                                <span style="color:var(--ink-soft)">Total Omzet</span>
                                <span class="font-bold font-mono" style="color:var(--success)">+ Rp
                                    {{ number_format($lifetimeRevenue, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex items-center justify-between text-xs py-1.5 border-b"
                                style="border-color:var(--line)">
                                <span style="color:var(--ink-soft)">Total Pengeluaran</span>
                                <span class="font-bold font-mono" style="color:var(--warn)">− Rp
                                    {{ number_format($lifetimeExpenses, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex items-center justify-between rounded-xl px-3 py-3 mt-1"
                                style="background:{{ $saldoRill >= 0 ? 'rgba(16,128,107,0.08)' : 'rgba(220,38,38,0.08)' }}">
                                <span class="text-xs font-bold"
                                    style="color:{{ $saldoRill >= 0 ? 'var(--success)' : 'var(--warn)' }}">Saldo
                                    Rill</span>
                                <span class="text-sm font-bold font-mono tabular-nums"
                                    style="color:{{ $saldoRill >= 0 ? 'var(--success)' : 'var(--warn)' }}">
                                    {{ $saldoRill < 0 ? '−' : '' }}Rp {{ number_format(abs($saldoRill), 0, ',', '.') }}
                                </span>
                            </div>
                            <p class="text-[9px] font-mono text-center pt-1" style="color:var(--ink-mute)">
                                = Total Modal − Total Pengeluaran
                            </p>
                        </div>
                    </div>

                </div>{{-- end bottom row --}}

            </div>
        </div>{{-- end capital distribution tracker --}}

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const ctx = document.getElementById('capital-dist-donut');
                if (!ctx) return;
                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Stok HP', 'Stok Aksesoris', 'Piutang Aktif', 'Saldo ATM', 'Uang Cash'],
                        datasets: [{
                            data: [{{ $distHP }}, {{ $distAcc }}, {{ $distPiu }},
                                {{ $distAtm }}, {{ $distKas }}
                            ],
                            backgroundColor: ['#7C3AED', '#0891B2', '#F59E0B', '#2563EB', '#10806B'],
                            borderWidth: 3,
                            borderColor: '#ffffff',
                            hoverOffset: 8
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                backgroundColor: '#1F2937',
                                padding: 10,
                                bodyFont: {
                                    size: 11,
                                    family: 'monospace'
                                },
                                titleFont: {
                                    size: 12,
                                    weight: 'bold'
                                },
                                callbacks: {
                                    label: function(ctx) {
                                        const val = ctx.raw;
                                        const total = ctx.dataset.data.reduce((a, b) => a + b, 0) || 1;
                                        const pct = ((val / total) * 100).toFixed(1);
                                        return '  Rp ' + val.toLocaleString('id-ID') + '  (' + pct + '%)';
                                    }
                                }
                            }
                        },
                        cutout: '70%'
                    }
                });
            });
        </script>


        {{-- Main Content: Cashflow + Modal --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">


            {{-- Modal Usaha (Capitals) --}}
            <div class="lg:col-span-10">
                <div class="bg-white rounded-xl border overflow-hidden shadow-sm" style="border-color:var(--line)">
                    <div class="px-5 py-4 border-b flex items-center justify-between" style="border-color:var(--line)">
                        <div>
                            <h3 class="text-sm font-semibold" style="color:var(--ink)">Modal & Pengeluaran</h3>
                            <p class="text-[11px] mt-0.5" style="color:var(--ink-mute)">Riwayat pemasukan modal dan
                                pengeluaran operasional</p>
                        </div>
                        @if (auth()->user()->role->value === 'superadmin')
                            <div class="flex items-center gap-2">
                                {{-- <button onclick="openExpenseModal()"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium transition-colors"
                                    style="background:#FFF5F5;color:var(--warn)"
                                    onmouseenter="this.style.background='#FEE2E2'"
                                    onmouseleave="this.style.background='#FFF5F5'">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                        stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                    </svg>
                                    Tambah Pengeluaran
                                </button> --}}
                                <button onclick="openModal('modal-kurangi-modal')"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium transition-colors"
                                    style="background:#FEF3C7;color:#92400E"
                                    onmouseenter="this.style.background='#FDE68A'"
                                    onmouseleave="this.style.background='#FEF3C7'">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                        stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4" />
                                    </svg>
                                    Kurangi Modal
                                </button>
                                <button onclick="openModal('modal-tambah-modal')"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium transition-colors"
                                    style="background:#EFF6FF;color:var(--accent)"
                                    onmouseenter="this.style.background='#DBEAFE'"
                                    onmouseleave="this.style.background='#EFF6FF'">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                        stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                    </svg>
                                    Tambah Modal
                                </button>
                            </div>
                        @endif
                    </div>

                    {{-- Capitals Log Table --}}
                    <div class="overflow-x-auto" style="max-height:240px;overflow-y:auto;">
                        <table class="w-full text-xs">
                            <thead class="sticky top-0" style="z-index:1">
                                <tr style="background:var(--bg-soft);border-bottom:1px solid var(--line)">
                                    <th class="text-left px-5 py-2.5 font-bold uppercase tracking-wider font-mono whitespace-nowrap"
                                        style="color:var(--ink-mute)">Tanggal</th>
                                    <th class="text-left px-5 py-2.5 font-bold uppercase tracking-wider font-mono"
                                        style="color:var(--ink-mute)">Keterangan</th>
                                    <th class="text-left px-5 py-2.5 font-bold uppercase tracking-wider font-mono"
                                        style="color:var(--ink-mute)">Jenis</th>
                                    <th class="text-right px-5 py-2.5 font-bold uppercase tracking-wider font-mono"
                                        style="color:var(--ink-mute)">Jumlah</th>
                                    @if (auth()->user()->role->value === 'superadmin')
                                        <th class="text-center px-5 py-2.5 font-bold uppercase tracking-wider font-mono"
                                            style="color:var(--ink-mute)">Aksi</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($capitals as $capital)
                                    <tr class="hover:bg-gray-50/60 transition-colors"
                                        style="border-bottom:1px solid var(--line)">
                                        <td class="px-5 py-2.5 font-mono whitespace-nowrap" style="color:var(--ink-soft)">
                                            {{ $capital->entry_date->format('d/m/Y') }}
                                        </td>
                                        <td class="px-5 py-2.5 font-medium" style="color:var(--ink)">
                                            {{ $capital->description }}
                                        </td>
                                        <td class="px-5 py-2.5">
                                            @if ($capital->type === 'initial')
                                                <span
                                                    class="px-2 py-0.5 rounded-full bg-blue-50 text-blue-700 font-semibold font-mono text-[9px]">Awal</span>
                                            @elseif ($capital->type === 'addition')
                                                <span
                                                    class="px-2 py-0.5 rounded-full bg-green-50 text-green-700 font-semibold font-mono text-[9px]">Tambahan</span>
                                            @elseif ($capital->type === 'withdrawal')
                                                <span
                                                    class="px-2 py-0.5 rounded-full bg-red-50 text-red-700 font-semibold font-mono text-[9px]">Pengurangan</span>
                                            @elseif ($capital->type === 'purchase')
                                                <span
                                                    class="px-2 py-0.5 rounded-full bg-purple-50 text-purple-700 font-semibold font-mono text-[9px]">Pembelian</span>
                                            @else
                                                <span
                                                    class="px-2 py-0.5 rounded-full bg-gray-50 text-gray-700 font-semibold font-mono text-[9px]">{{ $capital->type }}</span>
                                            @endif
                                        </td>
                                        <td class="px-5 py-2.5 text-right font-mono font-bold tabular-nums"
                                            style="{{ $capital->type === 'withdrawal' ? 'color:var(--warn)' : 'color:var(--ink)' }}">
                                            {{ $capital->type === 'withdrawal' ? '−' : '' }} Rp
                                            {{ number_format($capital->amount, 0, ',', '.') }}
                                        </td>
                                        @if (auth()->user()->role->value === 'superadmin')
                                            <td class="px-5 py-2.5 text-center">
                                                <form method="POST" action="{{ route('capitals.destroy', $capital) }}"
                                                    onsubmit="return confirm('Hapus entri ini?')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" title="Hapus"
                                                        class="inline-flex items-center justify-center w-7 h-7 rounded-lg transition-colors"
                                                        style="background:#FFF5F5;color:var(--warn)"
                                                        onmouseenter="this.style.background='#FEE2E2'"
                                                        onmouseleave="this.style.background='#FFF5F5'">
                                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                                                            stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ auth()->user()->role->value === 'superadmin' ? 5 : 4 }}"
                                            class="px-5 py-8 text-center text-xs" style="color:var(--ink-mute)">Belum ada
                                            data modal disetor</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($capitals->total() > 0)
                        <div class="px-5 py-3 flex items-center justify-between"
                            style="border-top:1px solid var(--line);background:var(--bg-soft)">
                            <span class="text-xs font-mono" style="color:var(--ink-mute)">{{ $capitals->total() }}
                                modal</span>
                            {{ $capitals->links() }}
                        </div>
                    @endif
                </div>
            </div>

        </div>

    </div>

    {{-- ========== MODAL: Tambah Modal ========== --}}
    @if (auth()->user()->role->value === 'superadmin')
        <div id="modal-tambah-modal" class="fixed inset-0 z-[100] hidden"
            onclick="closeModalOutside(event, 'modal-tambah-modal')">
            <div class="fixed inset-0" style="background:rgba(10,37,64,.5)"></div>
            <div class="fixed inset-0 flex items-center justify-center p-4 overflow-y-auto">
                <div class="w-full max-w-lg bg-white rounded-2xl shadow-2xl overflow-hidden modal-pop"
                    onclick="event.stopPropagation()">
                    <div class="flex items-start justify-between px-6 py-5" style="border-bottom:1px solid var(--line)">
                        <div class="flex items-center gap-3">
                            <span class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                                style="background:#EFF6FF;color:var(--accent)">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                    stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                </svg>
                            </span>
                            <div>
                                <h3 class="text-base font-semibold leading-none" style="color:var(--ink)">Tambah Modal
                                </h3>
                                <p class="text-xs mt-1.5" style="color:var(--ink-mute)">Catat modal yang masuk ke usaha
                                </p>
                            </div>
                        </div>
                        <button onclick="closeModal('modal-tambah-modal')"
                            class="w-8 h-8 flex items-center justify-center rounded-lg transition-colors"
                            style="color:var(--ink-mute);background:var(--bg-soft)"
                            onmouseenter="this.style.background='var(--line)'"
                            onmouseleave="this.style.background='var(--bg-soft)'">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <form method="POST" action="{{ route('capitals.store') }}" class="p-6 space-y-5">
                        @csrf
                        <div>
                            <label class="field-label">Keterangan <span style="color:var(--warn)">*</span></label>
                            <input type="text" name="description" placeholder="mis. Modal awal, tambahan modal..."
                                required class="field-input" />
                        </div>
                        <div>
                            <label class="field-label">Jumlah <span style="color:var(--warn)">*</span></label>
                            <div class="money-wrap">
                                <span class="rp-prefix">Rp</span>
                                <input type="text" name="amount" id="cap-amount" required placeholder="0"
                                    class="field-input money-input" inputmode="numeric"
                                    style="height:48px;font-size:16px" />
                            </div>
                            <div class="flex flex-wrap gap-2 mt-2.5">
                                @foreach ([500000 => '500rb', 1000000 => '1jt', 5000000 => '5jt', 10000000 => '10jt'] as $v => $lbl)
                                    <button type="button" onclick="setAmount('cap-amount',{{ $v }})"
                                        class="px-3 py-1.5 rounded-lg text-xs font-medium font-mono transition-colors"
                                        style="background:var(--bg-soft);color:var(--ink-soft)"
                                        onmouseenter="this.style.background='var(--line)'"
                                        onmouseleave="this.style.background='var(--bg-soft)'">{{ $lbl }}</button>
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
                                <input type="date" name="entry_date" value="{{ today()->toDateString() }}" required
                                    class="field-input" />
                            </div>
                        </div>
                        <div>
                            <label class="field-label">Metode Penyimpanan <span style="color:var(--warn)">*</span></label>
                            <div class="grid grid-cols-2 gap-3">
                                <label
                                    class="flex items-center gap-3 p-3 border rounded-xl cursor-pointer transition-colors hover:bg-gray-50"
                                    style="border-color:var(--line)" id="cap-cash-label">
                                    <input type="radio" name="payment_method" value="cash" required
                                        class="accent-blue-600" checked onchange="highlightCapMethod()" />
                                    <div>
                                        <div class="text-xs font-bold" style="color:var(--ink)">Kas Tunai</div>
                                        <div class="text-[10px]" style="color:var(--ink-mute)">Uang fisik / tunai</div>
                                    </div>
                                </label>
                                <label
                                    class="flex items-center gap-3 p-3 border rounded-xl cursor-pointer transition-colors hover:bg-gray-50"
                                    style="border-color:var(--line)" id="cap-transfer-label">
                                    <input type="radio" name="payment_method" value="transfer" class="accent-blue-600"
                                        onchange="highlightCapMethod()" />
                                    <div>
                                        <div class="text-xs font-bold" style="color:var(--ink)">Transfer / ATM</div>
                                        <div class="text-[10px]" style="color:var(--ink-mute)">Via rekening bank</div>
                                    </div>
                                </label>
                            </div>
                        </div>
                        <div class="flex gap-3 pt-1">
                            <button type="submit" class="btn-primary flex-1">Simpan Modal</button>
                            <button type="button" onclick="closeModal('modal-tambah-modal')" class="btn-secondary"
                                style="padding:0 24px">Batal</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- ========== MODAL: Kurangi Modal ========== --}}
        <div id="modal-kurangi-modal" class="fixed inset-0 z-[100] hidden"
            onclick="closeModalOutside(event, 'modal-kurangi-modal')">
            <div class="fixed inset-0" style="background:rgba(10,37,64,.5)"></div>
            <div class="fixed inset-0 flex items-center justify-center p-4 overflow-y-auto">
                <div class="w-full max-w-lg bg-white rounded-2xl shadow-2xl overflow-hidden modal-pop"
                    onclick="event.stopPropagation()">
                    <div class="flex items-start justify-between px-6 py-5" style="border-bottom:1px solid var(--line)">
                        <div class="flex items-center gap-3">
                            <span class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                                style="background:#FFF5F5;color:var(--warn)">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                    stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4" />
                                </svg>
                            </span>
                            <div>
                                <h3 class="text-base font-semibold leading-none" style="color:var(--ink)">Kurangi Modal
                                </h3>
                                <p class="text-xs mt-1.5" style="color:var(--ink-mute)">Catat penarikan atau pengurangan
                                    modal usaha</p>
                            </div>
                        </div>
                        <button onclick="closeModal('modal-kurangi-modal')"
                            class="w-8 h-8 flex items-center justify-center rounded-lg transition-colors"
                            style="color:var(--ink-mute);background:var(--bg-soft)"
                            onmouseenter="this.style.background='var(--line)'"
                            onmouseleave="this.style.background='var(--bg-soft)'">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <form method="POST" action="{{ route('capitals.store') }}" class="p-6 space-y-5">
                        @csrf
                        <input type="hidden" name="type" value="withdrawal" />
                        <div>
                            <label class="field-label">Keterangan <span style="color:var(--warn)">*</span></label>
                            <input type="text" name="description"
                                placeholder="mis. Penarikan pemilik, pengambilan modal..." required class="field-input" />
                        </div>
                        <div>
                            <label class="field-label">Jumlah yang Dikurangi <span
                                    style="color:var(--warn)">*</span></label>
                            <div class="money-wrap">
                                <span class="rp-prefix">Rp</span>
                                <input type="text" name="amount" id="withdraw-amount" required placeholder="0"
                                    class="field-input money-input" inputmode="numeric"
                                    style="height:48px;font-size:16px" />
                            </div>
                            <div class="flex flex-wrap gap-2 mt-2.5">
                                @foreach ([500000 => '500rb', 1000000 => '1jt', 5000000 => '5jt', 10000000 => '10jt'] as $v => $lbl)
                                    <button type="button" onclick="setAmount('withdraw-amount',{{ $v }})"
                                        class="px-3 py-1.5 rounded-lg text-xs font-medium font-mono transition-colors"
                                        style="background:var(--bg-soft);color:var(--ink-soft)"
                                        onmouseenter="this.style.background='var(--line)'"
                                        onmouseleave="this.style.background='var(--bg-soft)'">{{ $lbl }}</button>
                                @endforeach
                            </div>
                        </div>
                        <div>
                            <label class="field-label">Tanggal</label>
                            <input type="date" name="entry_date" value="{{ today()->toDateString() }}" required
                                class="field-input" />
                        </div>
                        <div class="flex gap-3 pt-1">
                            <button type="submit"
                                class="flex-1 inline-flex items-center justify-center gap-2 font-semibold rounded-xl transition-colors"
                                style="height:44px;font-size:14px;background:var(--warn);color:#fff"
                                onmouseenter="this.style.background='#dc2626'"
                                onmouseleave="this.style.background='var(--warn)'">
                                Kurangi Modal
                            </button>
                            <button type="button" onclick="closeModal('modal-kurangi-modal')" class="btn-secondary"
                                style="padding:0 24px">Batal</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        {{-- ========== MODAL: Tambah Pengeluaran ========== --}}
        <div id="modal-pengeluaran" class="fixed inset-0 z-[100] hidden" onclick="closePengeluaranOutside(event)">
            <div class="fixed inset-0" style="background:rgba(10,37,64,.5)"></div>
            <div class="fixed inset-0 flex items-center justify-center p-4 overflow-y-auto">
                <div class="w-full max-w-lg bg-white rounded-2xl shadow-2xl overflow-hidden modal-pop"
                    onclick="event.stopPropagation()">
                    <div class="flex items-start justify-between px-6 py-5" style="border-bottom:1px solid var(--line)">
                        <div class="flex items-center gap-3">
                            <span class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                                style="background:#FFF5F5;color:var(--warn)">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                    stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                </svg>
                            </span>
                            <div>
                                <h3 class="text-base font-semibold leading-none" style="color:var(--ink)">Tambah
                                    Pengeluaran</h3>
                                <p class="text-xs mt-1.5" style="color:var(--ink-mute)">Catat biaya operasional toko</p>
                            </div>
                        </div>
                        <button onclick="closePengeluaran()"
                            class="w-8 h-8 flex items-center justify-center rounded-lg transition-colors"
                            style="color:var(--ink-mute);background:var(--bg-soft)"
                            onmouseenter="this.style.background='var(--line)'"
                            onmouseleave="this.style.background='var(--bg-soft)'">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <form method="POST" action="{{ route('expenses.store') }}" class="p-6 space-y-5">
                        @csrf
                        <div>
                            <label class="field-label">Keterangan <span style="color:var(--warn)">*</span></label>
                            <input type="text" name="description" placeholder="mis. Bayar listrik, gaji, sewa..."
                                required class="field-input" />
                        </div>
                        {{-- Payment Method Toggle --}}
                        <div>
                            <label class="field-label">Metode Pembayaran <span style="color:var(--warn)">*</span></label>
                            <div class="grid grid-cols-2 gap-3 mt-1.5">
                                <label id="cf-exp-cash-label"
                                    class="flex items-center gap-3 p-3 border rounded-xl cursor-pointer transition-colors hover:bg-gray-50"
                                    style="border-color:var(--accent);background:rgba(37,99,235,0.03)">
                                    <input type="radio" name="payment_method" value="cash" checked
                                        class="accent-blue-600" onchange="highlightCfExpMethod()" />
                                    <div>
                                        <div class="text-xs font-bold" style="color:var(--ink)">Kas Tunai</div>
                                        <div class="text-[10px]" style="color:var(--ink-mute)">Bayar pakai uang tunai</div>
                                    </div>
                                </label>
                                <label id="cf-exp-transfer-label"
                                    class="flex items-center gap-3 p-3 border rounded-xl cursor-pointer transition-colors hover:bg-gray-50"
                                    style="border-color:var(--line)">
                                    <input type="radio" name="payment_method" value="transfer"
                                        class="accent-blue-600" onchange="highlightCfExpMethod()" />
                                    <div>
                                        <div class="text-xs font-bold" style="color:var(--ink)">Transfer / ATM</div>
                                        <div class="text-[10px]" style="color:var(--ink-mute)">Bayar via rekening bank</div>
                                    </div>
                                </label>
                            </div>
                        </div>
                        <div>
                            <label class="field-label">Jumlah <span style="color:var(--warn)">*</span></label>
                            <div class="money-wrap">
                                <span class="rp-prefix">Rp</span>
                                <input type="text" name="amount" id="exp-amount-cf" required placeholder="0"
                                    class="field-input money-input" inputmode="numeric"
                                    style="height:48px;font-size:16px" />
                            </div>
                            <div class="flex flex-wrap gap-2 mt-2.5">
                                @foreach ([20000 => '20rb', 50000 => '50rb', 100000 => '100rb', 500000 => '500rb'] as $v => $lbl)
                                    <button type="button" onclick="setCfExpAmount({{ $v }})"
                                        class="px-3 py-1.5 rounded-lg text-xs font-medium font-mono transition-colors"
                                        style="background:var(--bg-soft);color:var(--ink-soft)"
                                        onmouseenter="this.style.background='var(--line)'"
                                        onmouseleave="this.style.background='var(--bg-soft)'">{{ $lbl }}</button>
                                @endforeach
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="field-label">Kategori</label>
                                <select name="category" required class="field-input">
                                    <option value="operasional">Operasional</option>
                                    <option value="listrik">Listrik & Gas</option>
                                    <option value="gaji">Gaji</option>
                                    <option value="sewa">Sewa</option>
                                    <option value="lainnya">Lainnya</option>
                                </select>
                            </div>
                            <div>
                                <label class="field-label">Tanggal</label>
                                <input type="date" name="expense_date" value="{{ today()->toDateString() }}" required
                                    class="field-input" />
                            </div>
                        </div>
                        <div>
                            <label class="field-label">Catatan</label>
                            <textarea name="notes" rows="2" class="field-input" placeholder="Detail tambahan (opsional)"></textarea>
                        </div>
                        <div class="flex gap-3 pt-1">
                            <button type="submit" class="flex-1 btn-primary" style="background:var(--warn)">Simpan
                                Pengeluaran</button>
                            <button type="button" onclick="closePengeluaran()" class="btn-secondary"
                                style="padding:0 24px">Batal</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- ========== MODAL: Edit Pengeluaran (Cashflow) ========== --}}
        <div id="cf-modal-edit-expense" class="fixed inset-0 z-[110] hidden overflow-y-auto"
            onclick="if(event.target===this){closeCfEditExpenseModal()}">
            <div class="fixed inset-0" style="background:rgba(10,37,64,.5)"></div>
            <div class="relative min-h-full flex items-center justify-center px-4 pt-12 pb-12">
                <div class="w-full max-w-lg bg-white rounded-2xl shadow-2xl overflow-hidden modal-pop"
                    onclick="event.stopPropagation()">
                    <div class="flex items-start justify-between px-6 py-5"
                        style="border-bottom:1px solid var(--line)">
                        <div class="flex items-center gap-3">
                            <span class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                                style="background:#EFF6FF;color:var(--accent)">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </span>
                            <div>
                                <h3 class="text-base font-semibold leading-none" style="color:var(--ink)">Edit Pengeluaran</h3>
                                <p class="text-xs mt-1.5" style="color:var(--ink-mute)">Ubah data pengeluaran operasional</p>
                            </div>
                        </div>
                        <button onclick="closeCfEditExpenseModal()"
                            class="w-8 h-8 flex items-center justify-center rounded-lg transition-colors"
                            style="color:var(--ink-mute);background:var(--bg-soft)"
                            onmouseenter="this.style.background='var(--line)'"
                            onmouseleave="this.style.background='var(--bg-soft)'">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <form id="cf-edit-expense-form" method="POST" action="" class="p-6 space-y-5">
                        @csrf @method('PUT')
                        <div>
                            <label class="field-label">Keterangan <span style="color:var(--warn)">*</span></label>
                            <input type="text" name="description" id="cf-edit-exp-description" required class="field-input" />
                        </div>
                        <div>
                            <label class="field-label">Metode Pembayaran <span style="color:var(--warn)">*</span></label>
                            <div class="grid grid-cols-2 gap-3 mt-1.5">
                                <label id="cf-edit-exp-cash-label"
                                    class="flex items-center gap-3 p-3 border rounded-xl cursor-pointer transition-colors hover:bg-gray-50"
                                    style="border-color:var(--accent);background:rgba(37,99,235,0.03)">
                                    <input type="radio" name="payment_method" value="cash" id="cf-edit-exp-cash" checked
                                        class="accent-blue-600" onchange="highlightCfEditExpMethod()" />
                                    <div>
                                        <div class="text-xs font-bold" style="color:var(--ink)">Kas Tunai</div>
                                        <div class="text-[10px]" style="color:var(--ink-mute)">Bayar pakai uang tunai</div>
                                    </div>
                                </label>
                                <label id="cf-edit-exp-transfer-label"
                                    class="flex items-center gap-3 p-3 border rounded-xl cursor-pointer transition-colors hover:bg-gray-50"
                                    style="border-color:var(--line)">
                                    <input type="radio" name="payment_method" value="transfer" id="cf-edit-exp-transfer"
                                        class="accent-blue-600" onchange="highlightCfEditExpMethod()" />
                                    <div>
                                        <div class="text-xs font-bold" style="color:var(--ink)">Transfer / ATM</div>
                                        <div class="text-[10px]" style="color:var(--ink-mute)">Bayar via rekening bank</div>
                                    </div>
                                </label>
                            </div>
                        </div>
                        <div>
                            <label class="field-label">Jumlah <span style="color:var(--warn)">*</span></label>
                            <div class="money-wrap">
                                <span class="rp-prefix">Rp</span>
                                <input type="text" name="amount" id="cf-edit-exp-amount" required placeholder="0"
                                    class="field-input money-input" inputmode="numeric"
                                    style="height:48px;font-size:16px" />
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="field-label">Kategori</label>
                                <select name="category" id="cf-edit-exp-category" required class="field-input">
                                    <option value="operasional">Operasional</option>
                                    <option value="listrik">Listrik &amp; Gas</option>
                                    <option value="gaji">Gaji</option>
                                    <option value="sewa">Sewa</option>
                                    <option value="lainnya">Lainnya</option>
                                </select>
                            </div>
                            <div>
                                <label class="field-label">Tanggal</label>
                                <input type="date" name="expense_date" id="cf-edit-exp-date" required class="field-input" />
                            </div>
                        </div>
                        <div>
                            <label class="field-label">Catatan</label>
                            <textarea name="notes" id="cf-edit-exp-notes" rows="2" class="field-input" placeholder="Detail tambahan (opsional)"></textarea>
                        </div>
                        <div class="flex gap-3 pt-1">
                            <button type="submit" class="flex-1 btn-primary" style="background:var(--accent)">Simpan Perubahan</button>
                            <button type="button" onclick="closeCfEditExpenseModal()" class="btn-secondary" style="padding:0 24px">Batal</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        @include('components.money-format')
        <style>
            @keyframes modalPop {
                from {
                    opacity: 0;
                    transform: translateY(-12px) scale(.98);
                }

                to {
                    opacity: 1;
                    transform: translateY(0) scale(1);
                }
            }

            .modal-pop {
                animation: modalPop 200ms cubic-bezier(.16, 1, .3, 1);
            }
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
            if (el) {
                el.value = val.toLocaleString('id-ID');
            }
        }

        function openExpenseModal() {
            const el = document.getElementById('modal-pengeluaran');
            if (el) {
                el.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            }
            const first = el?.querySelector('input[name="description"]');
            if (first) setTimeout(() => first.focus(), 50);
        }

        function closePengeluaran() {
            const el = document.getElementById('modal-pengeluaran');
            if (el) {
                el.classList.add('hidden');
                document.body.style.overflow = '';
            }
        }

        function closePengeluaranOutside(e) {
            if (e.target === document.getElementById('modal-pengeluaran')) closePengeluaran();
        }

        function setCfExpAmount(val) {
            const el = document.getElementById('exp-amount-cf');
            if (el) el.value = val.toLocaleString('id-ID');
        }

        function highlightCfExpMethod() {
            const isCash = document.querySelector('#modal-pengeluaran input[name="payment_method"][value="cash"]')?.checked;
            const cashLabel = document.getElementById('cf-exp-cash-label');
            const transferLabel = document.getElementById('cf-exp-transfer-label');
            if (!cashLabel || !transferLabel) return;
            cashLabel.style.borderColor = isCash ? 'var(--accent)' : 'var(--line)';
            cashLabel.style.background = isCash ? 'rgba(37,99,235,0.03)' : '';
            transferLabel.style.borderColor = isCash ? 'var(--line)' : 'var(--accent)';
            transferLabel.style.background = isCash ? '' : 'rgba(37,99,235,0.03)';
        }

        function openCfEditExpenseModal(id, description, amount, category, date, notes, paymentMethod) {
            const form = document.getElementById('cf-edit-expense-form');
            form.action = '/expenses/' + id;
            document.getElementById('cf-edit-exp-description').value = description;
            document.getElementById('cf-edit-exp-amount').value = parseFloat(amount).toLocaleString('id-ID');
            document.getElementById('cf-edit-exp-category').value = category;
            document.getElementById('cf-edit-exp-date').value = date;
            document.getElementById('cf-edit-exp-notes').value = notes;
            const cashRadio = document.getElementById('cf-edit-exp-cash');
            const transferRadio = document.getElementById('cf-edit-exp-transfer');
            if (paymentMethod === 'transfer') {
                transferRadio.checked = true;
            } else {
                cashRadio.checked = true;
            }
            highlightCfEditExpMethod();
            document.getElementById('cf-modal-edit-expense').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            setTimeout(() => document.getElementById('cf-edit-exp-description').focus(), 50);
        }

        function closeCfEditExpenseModal() {
            document.getElementById('cf-modal-edit-expense').classList.add('hidden');
            document.body.style.overflow = '';
        }

        function highlightCfEditExpMethod() {
            const isCash = document.getElementById('cf-edit-exp-cash')?.checked;
            const cashLabel = document.getElementById('cf-edit-exp-cash-label');
            const transferLabel = document.getElementById('cf-edit-exp-transfer-label');
            if (!cashLabel || !transferLabel) return;
            cashLabel.style.borderColor = isCash ? 'var(--accent)' : 'var(--line)';
            cashLabel.style.background = isCash ? 'rgba(37,99,235,0.03)' : '';
            transferLabel.style.borderColor = isCash ? 'var(--line)' : 'var(--accent)';
            transferLabel.style.background = isCash ? '' : 'rgba(37,99,235,0.03)';
        }

        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') {
                ['modal-tambah-modal', 'modal-kurangi-modal'].forEach(closeModal);
                closePengeluaran();
                closeCfEditExpenseModal();
            }
        });

        function highlightCapMethod() {
            const cash = document.getElementById('cap-cash-label');
            const transfer = document.getElementById('cap-transfer-label');
            const isCash = document.querySelector('input[name="payment_method"][value="cash"]')?.checked;
            if (!cash || !transfer) return;
            const activeStyle = 'border-color:var(--accent);background:rgba(37,99,235,0.03)';
            const inactiveStyle = 'border-color:var(--line)';
            cash.style.cssText += ';' + (isCash ? activeStyle : inactiveStyle);
            transfer.style.cssText += ';' + (isCash ? inactiveStyle : activeStyle);
        }

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
