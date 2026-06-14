@extends('layouts.app')
@section('title', 'Laporan Keuangan')

@section('content')
    <div class="space-y-6">

        {{-- Title and Header --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold" style="color:var(--ink)">Laporan Keuangan</h2>
                <p class="text-xs mt-0.5" style="color:var(--ink-mute)">Analisis profitabilitas, arus kas operasional, dan
                    pengelolaan modal usaha</p>
            </div>

        </div>

        {{-- Advanced Period Filter Bar --}}
        <div class="bg-white rounded-xl border p-4 shadow-sm" style="border-color:var(--line)">
            <form method="GET" action="{{ route('reports.finance') }}" id="date-filter-form"
                class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <input type="hidden" name="preset" id="active-preset" value="{{ request('preset', 'all') }}" />

                {{-- Left: Inputs & Presets --}}
                <div class="flex flex-wrap items-center gap-4">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold" style="color:var(--ink-soft)">Periode:</span>
                        <div class="flex items-center gap-2 px-3 rounded-lg border bg-[#F8FAFC]"
                            style="border-color:var(--line); height: 36px;">
                            <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}"
                                class="text-xs focus:outline-none bg-transparent"
                                style="border: none !important; outline: none !important; box-shadow: none !important; padding: 0 !important; background: transparent; color: var(--ink); width: 115px;" />
                            <span class="text-xs" style="color:var(--ink-mute)">s/d</span>
                            <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}"
                                class="text-xs focus:outline-none bg-transparent"
                                style="border: none !important; outline: none !important; box-shadow: none !important; padding: 0 !important; background: transparent; color: var(--ink); width: 115px;" />
                        </div>
                    </div>

                    {{-- Presets Segmented Controls --}}
                    <div class="flex items-center gap-1 bg-gray-100 p-1 rounded-lg text-xs font-semibold"
                        style="height: 36px;">
                        @foreach (['today' => 'Hari Ini', 'week' => 'Minggu Ini', 'month' => 'Bulan Ini', 'all' => 'Semua'] as $p => $lbl)
                            @php $isActive = request('preset', 'all') === $p; @endphp
                            <button type="button" onclick="setPreset('{{ $p }}')"
                                class="px-3 rounded-md transition-all text-[11px] h-7 flex items-center justify-center {{ $isActive ? 'bg-white text-blue-600 font-bold shadow-sm' : 'text-gray-500 hover:text-gray-800' }}">
                                {{ $lbl }}
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- Right: Filter + Export buttons --}}
                <div class="flex items-center gap-2 shrink-0 self-end md:self-auto">
                    <button type="submit"
                        class="text-xs h-9 px-4 font-semibold rounded-lg transition-colors flex items-center gap-1.5 shadow-sm"
                        style="background:var(--accent);color:#fff" onmouseenter="this.style.filter='brightness(0.95)'"
                        onmouseleave="this.style.filter='none'">
                        Filter Periode
                    </button>
                    @if (auth()->user()->role->value === 'superadmin')
                        <a href="{{ route('reports.pdf', ['type' => 'finance', 'start_date' => request('start_date'), 'end_date' => request('end_date')]) }}"
                            target="_blank"
                            class="text-xs h-9 px-4 font-semibold rounded-lg transition-all flex items-center gap-1.5 border shadow-sm"
                            style="background:#EFF6FF;color:#1D4ED8;border-color:#BFDBFE"
                            onmouseenter="this.style.background='#DBEAFE'" onmouseleave="this.style.background='#EFF6FF'">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                stroke-width="2.2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                            Export PDF
                        </a>
                        <a href="{{ route('reports.export', ['type' => 'finance', 'start_date' => request('start_date'), 'end_date' => request('end_date')]) }}"
                            class="text-xs h-9 px-4 font-semibold rounded-lg transition-all flex items-center gap-1.5 border shadow-sm"
                            style="background:#F0FDF4;color:var(--success);border-color:#BBF7D0"
                            onmouseenter="this.style.background='#DCFCE7'" onmouseleave="this.style.background='#F0FDF4'">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                stroke-width="2.2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Export Excel
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <script>
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

        {{-- Stats Grid: Hari Ini, Minggu Ini, Bulan Ini --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">

            {{-- HARI INI (All Roles) --}}
            <div class="bg-white rounded-xl border p-5 shadow-sm hover:shadow transition-shadow"
                style="border-color:var(--line)">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[10px] font-bold uppercase tracking-widest font-mono"
                        style="color:var(--ink-mute)">Hari Ini (Daily)</span>
                    <span class="w-2 h-2 rounded-full" style="background:var(--accent)"></span>
                </div>
                <div class="space-y-2.5">
                    <div>
                        <span class="text-[11px] font-medium" style="color:var(--ink-soft)">Omzet</span>
                        <div class="text-xl font-bold font-mono tracking-tight tabular-nums" style="color:var(--ink)">
                            Rp {{ number_format($today['revenue'], 0, ',', '.') }}
                        </div>
                    </div>
                    <div class="pt-2 border-t" style="border-color:var(--line)">
                        <span class="text-[11px] font-medium" style="color:var(--ink-soft)">Laba Bersih</span>
                        <div class="text-xl font-bold font-mono tracking-tight tabular-nums" style="color:var(--success)">
                            Rp {{ number_format($today['profit'], 0, ',', '.') }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- MINGGU INI (Superadmin Only / Admin Locked) --}}
            @if (auth()->user()->role->value === 'superadmin')
                <div class="bg-white rounded-xl border p-5 shadow-sm hover:shadow transition-shadow"
                    style="border-color:var(--line)">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-[10px] font-bold uppercase tracking-widest font-mono"
                            style="color:var(--ink-mute)">Minggu Ini (Weekly)</span>
                        <span class="w-2 h-2 rounded-full" style="background:var(--success)"></span>
                    </div>
                    <div class="space-y-2.5">
                        <div>
                            <span class="text-[11px] font-medium" style="color:var(--ink-soft)">Omzet</span>
                            <div class="text-xl font-bold font-mono tracking-tight tabular-nums" style="color:var(--ink)">
                                Rp {{ number_format($week['revenue'], 0, ',', '.') }}
                            </div>
                        </div>
                        <div class="pt-2 border-t" style="border-color:var(--line)">
                            <span class="text-[11px] font-medium" style="color:var(--ink-soft)">Laba Bersih</span>
                            <div class="text-xl font-bold font-mono tracking-tight tabular-nums"
                                style="color:var(--success)">
                                Rp {{ number_format($week['profit'], 0, ',', '.') }}
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="bg-white rounded-xl border p-5 shadow-sm relative overflow-hidden flex flex-col justify-center border-dashed"
                    style="border-color:var(--line); min-height: 150px;">
                    <div
                        class="absolute inset-0 bg-white/70 backdrop-blur-[1px] flex flex-col items-center justify-center text-center p-4">
                        <svg class="w-5 h-5 mb-1.5" style="color:var(--ink-mute)" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                        <span class="text-[11px] font-semibold" style="color:var(--ink-soft)">Minggu Ini (Weekly)</span>
                        <span class="text-[9px] uppercase tracking-wider font-mono font-bold mt-0.5"
                            style="color:var(--accent)">Superadmin Only</span>
                    </div>
                </div>
            @endif

            {{-- BULAN INI (Superadmin Only / Admin Locked) --}}
            @if (auth()->user()->role->value === 'superadmin')
                <div class="bg-white rounded-xl border p-5 shadow-sm hover:shadow transition-shadow"
                    style="border-color:var(--line)">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-[10px] font-bold uppercase tracking-widest font-mono"
                            style="color:var(--ink-mute)">Bulan Ini (Monthly)</span>
                        <span class="w-2 h-2 rounded-full" style="background:#EF4444"></span>
                    </div>
                    <div class="space-y-2.5">
                        <div>
                            <span class="text-[11px] font-medium" style="color:var(--ink-soft)">Omzet</span>
                            <div class="text-xl font-bold font-mono tracking-tight tabular-nums" style="color:var(--ink)">
                                Rp {{ number_format($month['revenue'], 0, ',', '.') }}
                            </div>
                        </div>
                        <div class="pt-2 border-t" style="border-color:var(--line)">
                            <span class="text-[11px] font-medium" style="color:var(--ink-soft)">Laba Bersih</span>
                            <div class="text-xl font-bold font-mono tracking-tight tabular-nums"
                                style="color:var(--success)">
                                Rp {{ number_format($month['profit'], 0, ',', '.') }}
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="bg-white rounded-xl border p-5 shadow-sm relative overflow-hidden flex flex-col justify-center border-dashed"
                    style="border-color:var(--line); min-height: 150px;">
                    <div
                        class="absolute inset-0 bg-white/70 backdrop-blur-[1px] flex flex-col items-center justify-center text-center p-4">
                        <svg class="w-5 h-5 mb-1.5" style="color:var(--ink-mute)" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                        <span class="text-[11px] font-semibold" style="color:var(--ink-soft)">Bulan Ini (Monthly)</span>
                        <span class="text-[9px] uppercase tracking-wider font-mono font-bold mt-0.5"
                            style="color:var(--accent)">Superadmin Only</span>
                    </div>
                </div>
            @endif

        </div>
        {{-- Asset Overview (Superadmin) --}}
        @if (auth()->user()->role->value === 'superadmin')
            @php $totalAset = ($assetValue ?? 0) + ($accAssetValue ?? 0); @endphp
            <div class="bg-white rounded-xl border shadow-sm overflow-hidden" style="border-color:var(--line)">
                <div class="px-5 py-4 border-b" style="border-color:var(--line);background:var(--bg-soft)">
                    <h3 class="text-sm font-semibold" style="color:var(--ink)">Ringkasan Aset Stok</h3>
                    <p class="text-[11px] mt-0.5" style="color:var(--ink-mute)">Nilai modal yang tertanam di stok HP dan
                        aksesoris saat ini</p>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 divide-y sm:divide-y-0 sm:divide-x"
                    style="border-color:var(--line)">
                    <div class="px-6 py-5">
                        <div class="flex items-center gap-2.5 mb-2">
                            <span class="w-7 h-7 rounded-lg flex items-center justify-center flex-shrink-0"
                                style="background:rgba(124,58,237,0.08)">
                                <svg class="w-3.5 h-3.5 text-violet-500" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                            </span>
                            <span class="text-[10px] font-bold uppercase tracking-widest font-mono text-violet-500">Stok
                                HP</span>
                        </div>
                        <p class="text-xl font-bold font-mono tabular-nums text-violet-600">
                            Rp {{ number_format($assetValue ?? 0, 0, ',', '.') }}
                        </p>
                        <p class="text-[10px] mt-1" style="color:var(--ink-mute)">Nilai beli HP ready di stok</p>
                    </div>
                    <div class="px-6 py-5">
                        <div class="flex items-center gap-2.5 mb-2">
                            <span class="w-7 h-7 rounded-lg flex items-center justify-center flex-shrink-0"
                                style="background:rgba(14,116,144,0.08)">
                                <svg class="w-3.5 h-3.5 text-cyan-600" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                            </span>
                            <span class="text-[10px] font-bold uppercase tracking-widest font-mono text-cyan-600">Stok
                                Aksesoris</span>
                        </div>
                        <p class="text-xl font-bold font-mono tabular-nums text-cyan-700">
                            Rp {{ number_format($accAssetValue ?? 0, 0, ',', '.') }}
                        </p>
                        <p class="text-[10px] mt-1" style="color:var(--ink-mute)">Nilai modal aksesoris di stok</p>
                    </div>
                    <div class="px-6 py-5">
                        <div class="flex items-center gap-2.5 mb-2">
                            <span class="w-7 h-7 rounded-lg flex items-center justify-center flex-shrink-0"
                                style="background:rgba(16,128,107,0.1)">
                                <svg class="w-3.5 h-3.5" style="color:var(--success)" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </span>
                            <span class="text-[10px] font-bold uppercase tracking-widest font-mono"
                                style="color:var(--success)">Total Aset Stok</span>
                        </div>
                        <p class="text-xl font-bold font-mono tabular-nums" style="color:var(--success)">
                            Rp {{ number_format($totalAset, 0, ',', '.') }}
                        </p>
                        <p class="text-[10px] mt-1" style="color:var(--ink-mute)">HP + Aksesoris gabungan</p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Capitals and Expenses for Superadmin --}}
        @if (auth()->user()->role->value === 'superadmin')
            <div class="space-y-6 mt-6">

                {{-- Operational Expenses Log --}}
                <div class="bg-white rounded-xl border overflow-hidden shadow-sm" style="border-color:var(--line)">
                    <div class="px-5 py-4 border-b flex items-center justify-between" style="border-color:var(--line)">
                        <div>
                            <h3 class="text-sm font-semibold" style="color:var(--ink)">Biaya Pengeluaran Operasional (Gas,
                                Listrik, Dll)</h3>
                            <p class="text-[11px] mt-0.5" style="color:var(--ink-mute)">Log biaya pengeluaran operasional
                                toko yang mengurangi modal sekarang</p>
                        </div>

                        <button onclick="openExpenseModal()"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors"
                            style="background:#FFF5F5;color:var(--warn)" onmouseenter="this.style.background='#FEE2E2'"
                            onmouseleave="this.style.background='#FFF5F5'">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                            </svg>
                            Tambah Pengeluaran
                        </button>
                    </div>

                    <div class="overflow-x-auto max-h-[300px]">
                        <table class="w-full text-xs">
                            <thead>
                                <tr style="background:var(--bg-soft); border-bottom:1px solid var(--line)">
                                    <th class="text-left px-5 py-2.5 font-bold uppercase tracking-wider font-mono"
                                        style="color:var(--ink-mute)">Tanggal</th>
                                    <th class="text-left px-5 py-2.5 font-bold uppercase tracking-wider font-mono"
                                        style="color:var(--ink-mute)">Keterangan</th>
                                    <th class="text-left px-5 py-2.5 font-bold uppercase tracking-wider font-mono"
                                        style="color:var(--ink-mute)">Kategori</th>
                                    <th class="text-left px-5 py-2.5 font-bold uppercase tracking-wider font-mono"
                                        style="color:var(--ink-mute)">Metode</th>
                                    <th class="text-left px-5 py-2.5 font-bold uppercase tracking-wider font-mono"
                                        style="color:var(--ink-mute)">Catatan</th>
                                    <th class="text-right px-5 py-2.5 font-bold uppercase tracking-wider font-mono"
                                        style="color:var(--ink-mute)">Jumlah</th>
                                    <th class="text-center px-5 py-2.5 font-bold uppercase tracking-wider font-mono"
                                        style="color:var(--ink-mute)">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                    @forelse($expenses as $expense)
                                        <tr style="border-bottom:1px solid var(--line)">
                                            <td class="px-5 py-3 font-mono" style="color:var(--ink-soft)">
                                                {{ $expense->expense_date->format('d/m/Y') }}</td>
                                            <td class="px-5 py-3 font-medium" style="color:var(--ink)">
                                                {{ $expense->description }}</td>
                                            <td class="px-5 py-3">
                                                <span
                                                    class="px-2 py-0.5 rounded bg-gray-100 text-gray-700 font-mono text-[9px] capitalize">{{ $expense->category }}</span>
                                            </td>
                                            <td class="px-5 py-3">
                                                @if (($expense->payment_method ?? 'cash') === 'transfer')
                                                    <span class="px-2 py-0.5 rounded-full bg-blue-50 text-blue-600 font-mono text-[9px] font-semibold">Transfer</span>
                                                @else
                                                    <span class="px-2 py-0.5 rounded-full bg-green-50 text-green-600 font-mono text-[9px] font-semibold">Tunai</span>
                                                @endif
                                            </td>
                                            <td class="px-5 py-3" style="color:var(--ink-mute)">{{ $expense->notes ?: '—' }}
                                            </td>
                                            <td class="px-5 py-3 text-right font-mono font-bold tabular-nums"
                                                style="color:var(--warn)">
                                                Rp {{ number_format($expense->amount, 0, ',', '.') }}
                                            </td>
                                            <td class="px-5 py-3 text-center">
                                                <div class="flex items-center justify-center gap-1.5">
                                                    <button type="button"
                                                        onclick="openEditExpenseModal({{ $expense->id }}, '{{ addslashes($expense->description) }}', {{ $expense->amount }}, '{{ $expense->category }}', '{{ $expense->expense_date->format('Y-m-d') }}', '{{ addslashes($expense->notes ?? '') }}', '{{ $expense->payment_method ?? 'cash' }}')"
                                                        title="Edit"
                                                        class="inline-flex items-center justify-center w-7 h-7 rounded-lg transition-colors"
                                                        style="background:#EFF6FF;color:var(--accent)"
                                                        onmouseenter="this.style.background='#DBEAFE'"
                                                        onmouseleave="this.style.background='#EFF6FF'">
                                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                                                            stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                        </svg>
                                                    </button>
                                                    <form method="POST" action="{{ route('expenses.destroy', $expense) }}"
                                                        onsubmit="return confirm('Hapus pengeluaran ini?')">
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
                                                </div>
                                            </td>
                                        </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-5 py-8 text-center text-xs"
                                            style="color:var(--ink-mute)">Belum ada data pengeluaran dicatat</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if ($expenses->total() > 0)
                        <div class="px-5 py-3 flex items-center justify-between"
                            style="border-top:1px solid var(--line);background:var(--bg-soft)">
                            <span class="text-xs font-mono" style="color:var(--ink-mute)">{{ $expenses->total() }}
                                pengeluaran</span>
                            {{ $expenses->appends(request()->query())->links() }}
                        </div>
                    @endif
                </div>

            </div>

            {{-- ========== MODAL: Tambah Pengeluaran (Quick Form) ========== --}}
            <div id="modal-pengeluaran-quick" class="fixed inset-0 z-[100] hidden overflow-y-auto"
                onclick="closeExpenseModalOutside(event)">
                <div class="fixed inset-0" style="background:rgba(10,37,64,.5)"></div>
                <div class="relative min-h-full flex items-center justify-center px-4 pt-12 pb-12">
                    <div class="w-full max-w-lg bg-white rounded-2xl shadow-2xl overflow-hidden modal-pop"
                        onclick="event.stopPropagation()">

                        <div class="flex items-start justify-between px-6 py-5"
                            style="border-bottom:1px solid var(--line)">
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
                                    <p class="text-xs mt-1.5" style="color:var(--ink-mute)">Catat pengeluaran listrik,
                                        gas, gaji, dan biaya operasional</p>
                                </div>
                            </div>
                            <button onclick="closeExpenseModal()"
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
                                <input type="text" name="description"
                                    placeholder="mis. Bayar listrik, Isi ulang tabung gas, Gaji admin..." required
                                    class="field-input" />
                            </div>
                            {{-- Payment Method Toggle --}}
                            <div>
                                <label class="field-label">Metode Pembayaran <span style="color:var(--warn)">*</span></label>
                                <div class="grid grid-cols-2 gap-3 mt-1.5">
                                    <label id="fin-exp-cash-label"
                                        class="flex items-center gap-3 p-3 border rounded-xl cursor-pointer transition-colors hover:bg-gray-50"
                                        style="border-color:var(--accent);background:rgba(37,99,235,0.03)">
                                        <input type="radio" name="payment_method" value="cash" checked
                                            class="accent-blue-600" onchange="highlightFinExpMethod()" />
                                        <div>
                                            <div class="text-xs font-bold" style="color:var(--ink)">Kas Tunai</div>
                                            <div class="text-[10px]" style="color:var(--ink-mute)">Bayar pakai uang tunai</div>
                                        </div>
                                    </label>
                                    <label id="fin-exp-transfer-label"
                                        class="flex items-center gap-3 p-3 border rounded-xl cursor-pointer transition-colors hover:bg-gray-50"
                                        style="border-color:var(--line)">
                                        <input type="radio" name="payment_method" value="transfer"
                                            class="accent-blue-600" onchange="highlightFinExpMethod()" />
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
                                    <input type="text" name="amount" id="quick-exp-amount" required placeholder="0"
                                        class="field-input money-input" inputmode="numeric"
                                        style="height:48px;font-size:16px" />
                                </div>
                                <div class="flex flex-wrap gap-2 mt-2.5">
                                    @foreach ([20000 => '20rb', 50000 => '50rb', 100000 => '100rb', 500000 => '500rb'] as $v => $lbl)
                                        <button type="button" onclick="setQuickAmount({{ $v }})"
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
                                    <input type="date" name="expense_date" value="{{ today()->toDateString() }}"
                                        required class="field-input" />
                                </div>
                            </div>
                            <div>
                                <label class="field-label">Catatan</label>
                                <textarea name="notes" rows="2" class="field-input" placeholder="Detail tambahan (opsional)"></textarea>
                            </div>
                            <div class="flex gap-3 pt-1">
                                <button type="submit" class="btn-primary flex-1" style="background:var(--warn)">Simpan
                                    Pengeluaran</button>
                                <button type="button" onclick="closeExpenseModal()" class="btn-secondary"
                                    style="padding:0 24px">Batal</button>
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
                function openExpenseModal() {
                    const el = document.getElementById('modal-pengeluaran-quick');
                    if (el) {
                        el.classList.remove('hidden');
                        document.body.style.overflow = 'hidden';
                        const first = el.querySelector('input[name="description"]');
                        if (first) setTimeout(() => first.focus(), 50);
                    }
                }

                function closeExpenseModal() {
                    const el = document.getElementById('modal-pengeluaran-quick');
                    if (el) {
                        el.classList.add('hidden');
                        document.body.style.overflow = '';
                    }
                }

                function closeExpenseModalOutside(e) {
                    if (e.target === document.getElementById('modal-pengeluaran-quick')) closeExpenseModal();
                }

                function setQuickAmount(val) {
                    const el = document.getElementById('quick-exp-amount');
                    if (el) {
                        el.value = val.toLocaleString('id-ID');
                    }
                }

                function highlightFinExpMethod() {
                    const isCash = document.querySelector('#modal-pengeluaran-quick input[name="payment_method"][value="cash"]')?.checked;
                    const cashLabel = document.getElementById('fin-exp-cash-label');
                    const transferLabel = document.getElementById('fin-exp-transfer-label');
                    if (!cashLabel || !transferLabel) return;
                    cashLabel.style.borderColor = isCash ? 'var(--accent)' : 'var(--line)';
                    cashLabel.style.background = isCash ? 'rgba(37,99,235,0.03)' : '';
                    transferLabel.style.borderColor = isCash ? 'var(--line)' : 'var(--accent)';
                    transferLabel.style.background = isCash ? '' : 'rgba(37,99,235,0.03)';
                }

                function openEditExpenseModal(id, description, amount, category, date, notes, paymentMethod) {
                    const form = document.getElementById('edit-expense-form');
                    form.action = '/expenses/' + id;
                    document.getElementById('edit-exp-description').value = description;
                    document.getElementById('edit-exp-amount').value = parseFloat(amount).toLocaleString('id-ID');
                    document.getElementById('edit-exp-category').value = category;
                    document.getElementById('edit-exp-date').value = date;
                    document.getElementById('edit-exp-notes').value = notes;
                    const cashRadio = document.getElementById('edit-exp-cash');
                    const transferRadio = document.getElementById('edit-exp-transfer');
                    if (paymentMethod === 'transfer') {
                        transferRadio.checked = true;
                    } else {
                        cashRadio.checked = true;
                    }
                    highlightEditExpMethod();
                    const modal = document.getElementById('modal-edit-expense');
                    modal.classList.remove('hidden');
                    document.body.style.overflow = 'hidden';
                    setTimeout(() => document.getElementById('edit-exp-description').focus(), 50);
                }

                function closeEditExpenseModal() {
                    document.getElementById('modal-edit-expense').classList.add('hidden');
                    document.body.style.overflow = '';
                }

                function highlightEditExpMethod() {
                    const isCash = document.getElementById('edit-exp-cash')?.checked;
                    const cashLabel = document.getElementById('edit-exp-cash-label');
                    const transferLabel = document.getElementById('edit-exp-transfer-label');
                    if (!cashLabel || !transferLabel) return;
                    cashLabel.style.borderColor = isCash ? 'var(--accent)' : 'var(--line)';
                    cashLabel.style.background = isCash ? 'rgba(37,99,235,0.03)' : '';
                    transferLabel.style.borderColor = isCash ? 'var(--line)' : 'var(--accent)';
                    transferLabel.style.background = isCash ? '' : 'rgba(37,99,235,0.03)';
                }

                document.addEventListener('keydown', e => {
                    if (e.key === 'Escape') {
                        closeExpenseModal();
                        closeEditExpenseModal();
                    }
                });
            </script>

        {{-- ========== MODAL: Edit Pengeluaran ========== --}}
        <div id="modal-edit-expense" class="fixed inset-0 z-[110] hidden overflow-y-auto"
            onclick="if(event.target===this){closeEditExpenseModal()}">
            <div class="fixed inset-0" style="background:rgba(10,37,64,.5)"></div>
            <div class="relative min-h-full flex items-center justify-center px-4 pt-12 pb-12">
                <div class="w-full max-w-lg bg-white rounded-2xl shadow-2xl overflow-hidden modal-pop"
                    onclick="event.stopPropagation()">
                    <div class="flex items-start justify-between px-6 py-5"
                        style="border-bottom:1px solid var(--line)">
                        <div class="flex items-center gap-3">
                            <span class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                                style="background:#EFF6FF;color:var(--accent)">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                    stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </span>
                            <div>
                                <h3 class="text-base font-semibold leading-none" style="color:var(--ink)">Edit Pengeluaran</h3>
                                <p class="text-xs mt-1.5" style="color:var(--ink-mute)">Ubah data pengeluaran operasional</p>
                            </div>
                        </div>
                        <button onclick="closeEditExpenseModal()"
                            class="w-8 h-8 flex items-center justify-center rounded-lg transition-colors"
                            style="color:var(--ink-mute);background:var(--bg-soft)"
                            onmouseenter="this.style.background='var(--line)'"
                            onmouseleave="this.style.background='var(--bg-soft)'">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <form id="edit-expense-form" method="POST" action="" class="p-6 space-y-5">
                        @csrf @method('PUT')
                        <div>
                            <label class="field-label">Keterangan <span style="color:var(--warn)">*</span></label>
                            <input type="text" name="description" id="edit-exp-description" required class="field-input" />
                        </div>
                        {{-- Payment Method Toggle --}}
                        <div>
                            <label class="field-label">Metode Pembayaran <span style="color:var(--warn)">*</span></label>
                            <div class="grid grid-cols-2 gap-3 mt-1.5">
                                <label id="edit-exp-cash-label"
                                    class="flex items-center gap-3 p-3 border rounded-xl cursor-pointer transition-colors hover:bg-gray-50"
                                    style="border-color:var(--accent);background:rgba(37,99,235,0.03)">
                                    <input type="radio" name="payment_method" value="cash" id="edit-exp-cash" checked
                                        class="accent-blue-600" onchange="highlightEditExpMethod()" />
                                    <div>
                                        <div class="text-xs font-bold" style="color:var(--ink)">Kas Tunai</div>
                                        <div class="text-[10px]" style="color:var(--ink-mute)">Bayar pakai uang tunai</div>
                                    </div>
                                </label>
                                <label id="edit-exp-transfer-label"
                                    class="flex items-center gap-3 p-3 border rounded-xl cursor-pointer transition-colors hover:bg-gray-50"
                                    style="border-color:var(--line)">
                                    <input type="radio" name="payment_method" value="transfer" id="edit-exp-transfer"
                                        class="accent-blue-600" onchange="highlightEditExpMethod()" />
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
                                <input type="text" name="amount" id="edit-exp-amount" required placeholder="0"
                                    class="field-input money-input" inputmode="numeric"
                                    style="height:48px;font-size:16px" />
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="field-label">Kategori</label>
                                <select name="category" id="edit-exp-category" required class="field-input">
                                    <option value="operasional">Operasional</option>
                                    <option value="listrik">Listrik &amp; Gas</option>
                                    <option value="gaji">Gaji</option>
                                    <option value="sewa">Sewa</option>
                                    <option value="lainnya">Lainnya</option>
                                </select>
                            </div>
                            <div>
                                <label class="field-label">Tanggal</label>
                                <input type="date" name="expense_date" id="edit-exp-date" required class="field-input" />
                            </div>
                        </div>
                        <div>
                            <label class="field-label">Catatan</label>
                            <textarea name="notes" id="edit-exp-notes" rows="2" class="field-input" placeholder="Detail tambahan (opsional)"></textarea>
                        </div>
                        <div class="flex gap-3 pt-1">
                            <button type="submit" class="btn-primary flex-1" style="background:var(--accent)">Simpan Perubahan</button>
                            <button type="button" onclick="closeEditExpenseModal()" class="btn-secondary" style="padding:0 24px">Batal</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        @endif
    </div>
@endsection
