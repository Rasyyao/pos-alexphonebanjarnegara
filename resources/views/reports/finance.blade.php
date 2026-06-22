@extends('layouts.app')
@section('title', 'Laporan Keuangan')

@section('content')
    <div class="space-y-6">

        {{-- Title and Header --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold" style="color:var(--ink)">Laporan Harian {{ $today['date_label'] ?? now()->format('d/m/Y') }}</h2>
                <p class="text-xs mt-0.5" style="color:var(--ink-mute)">Analisis profitabilitas, arus kas operasional, dan
                    pengelolaan modal usaha</p>
            </div>
            
            <div class="flex items-center gap-2">
                @php
                    $isSuperadmin = auth()->user()->role->value === 'superadmin';
                    $canManageDailyClosing = in_array(auth()->user()->role->value, ['superadmin', 'admin']);
                    $viewedDate = (request('start_date') && request('start_date') === request('end_date')) ? request('start_date') : today()->toDateString();
                    $closingRecord = \App\Models\DailyClosing::whereDate('closing_date', $viewedDate)->first();
                    $isLocked = !$isSuperadmin && $closingRecord && in_array($closingRecord->status, ['closed', 'verified']);
                @endphp

                @if ($closingRecord && in_array($closingRecord->status, ['closed', 'verified']))
                    <div class="flex items-center gap-2">
                        @if ($closingRecord->status === 'closed')
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-amber-100 text-amber-800 border border-amber-200 shadow-sm">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                                Tutup Buku (Pending)
                            </span>
                        @elseif ($closingRecord->status === 'verified')
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200 shadow-sm">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                                Tutup Buku (Verified)
                            </span>
                        @endif

                        @if ($isSuperadmin)
                            <form method="POST" action="{{ route('daily-closings.revert', $closingRecord) }}"
                                  onsubmit="return confirm('Kembalikan laporan ini ke draft? Tanggal transaksi akan dibuka kembali.')">
                                @csrf
                                <button type="submit"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold text-gray-700 bg-white border hover:bg-gray-50 transition-all shadow-sm"
                                    style="border-color:var(--line)">
                                    <svg class="w-3.5 h-3.5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 11V7a4 4 0 118 0m-4 10v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z" />
                                    </svg>
                                    Buka Kembali
                                </button>
                            </form>
                        @endif
                    </div>
                @elseif ($canManageDailyClosing)
                    <button type="button" onclick="openClosingModal('{{ $viewedDate }}')"
                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-xs font-bold text-white transition-all shadow-sm"
                        style="background:var(--accent)"
                        onmouseenter="this.style.filter='brightness(0.95)'"
                        onmouseleave="this.style.filter='none'">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                        Tutup Buku
                    </button>
                @endif
            </div>
        </div>

        @if ($closingRecord && in_array($closingRecord->status, ['closed', 'verified']))
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 flex items-start gap-3 shadow-sm">
                <div class="p-2 rounded-lg bg-amber-100 text-amber-800 flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
                <div>
                    <h4 class="text-sm font-bold text-amber-900">Laporan Keuangan Terkunci (Tutup Buku)</h4>
                    @if ($closingRecord->status === 'closed')
                        <p class="text-xs text-amber-800 mt-1">
                            Laporan keuangan untuk tanggal <strong>{{ \Carbon\Carbon::parse($viewedDate)->format('d/m/Y') }}</strong> telah ditutup oleh <strong>{{ $closingRecord->closedBy->name ?? 'Admin' }}</strong> pada {{ $closingRecord->closed_at->format('d/m/Y H:i') }}.
                            Semua mutasi transaksi pada tanggal ini dikunci dan tidak dapat diubah kecuali oleh Super Admin. @if($isSuperadmin)<strong>(Anda login sebagai Super Admin dan tetap dapat melakukan pengeditan)</strong>@endif
                        </p>
                    @else
                        <p class="text-xs text-amber-800 mt-1">
                            Laporan keuangan untuk tanggal <strong>{{ \Carbon\Carbon::parse($viewedDate)->format('d/m/Y') }}</strong> telah diverifikasi oleh Super Admin <strong>{{ $closingRecord->verifiedBy->name ?? 'Super Admin' }}</strong> pada {{ $closingRecord->verified_at->format('d/m/Y H:i') }}.
                            @if ($isSuperadmin)
                                Semua data transaksi dikunci untuk Admin, tetapi sebagai <strong>Super Admin</strong> Anda tetap dapat melakukan pengeditan.
                            @else
                                Semua data transaksi bersifat read-only.
                            @endif
                        </p>
                    @endif
                    @if ($isSuperadmin)
                        <div class="mt-3">
                            <form method="POST" action="{{ route('daily-closings.revert', $closingRecord) }}"
                                  onsubmit="return confirm('Kembalikan laporan ini ke draft? Tanggal transaksi akan dibuka kembali.')">
                                @csrf
                                <button type="submit"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold bg-amber-600 hover:bg-amber-700 text-white transition-all shadow-sm">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 11V7a4 4 0 118 0m-4 10v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z" />
                                    </svg>
                                    Buka Kunci Tutup Buku
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        {{-- Advanced Period Filter Bar --}}
        <div class="bg-white rounded-xl border p-4 shadow-sm" style="border-color:var(--line)">
            <form method="GET" action="{{ route('reports.finance') }}" id="date-filter-form"
                class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <input type="hidden" name="preset" id="active-preset" value="{{ request('preset', 'all') }}" />
                <input type="hidden" name="type_filter" id="active-type-filter" value="{{ request('type_filter', 'all') }}" />

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
                    @if (in_array(auth()->user()->role->value, ['superadmin', 'admin']))
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

            function setTypeFilter(filter) {
                document.getElementById('active-type-filter').value = filter;
                document.getElementById('date-filter-form').submit();
            }
        </script>

        <div class="grid grid-cols-2 {{ $isSuperadmin ? 'lg:grid-cols-6' : 'lg:grid-cols-5' }} gap-3">

            {{-- 1. Omzet --}}
            <div class="bg-white rounded-xl border p-5 card-lift"
                style="border-color:var(--line)">
                <div class="flex items-start justify-between mb-3">
                    <div class="text-[11px] font-medium uppercase tracking-widest font-mono" style="color:var(--ink-mute)">Omzet</div>
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0"
                        style="background:rgba(37,99,235,0.08)">
                        <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                    </div>
                </div>
                <div class="text-2xl font-semibold leading-none mb-1 font-mono tabular-nums text-blue-600" style="color:#2563EB">
                    Rp {{ number_format($today['revenue'], 0, ',', '.') }}
                </div>
                <div class="text-xs" style="color:var(--ink-mute)">Penjualan disetujui tanggal {{ $today['date_label'] }}</div>
            </div>

            {{-- 2. Cash --}}
            <div class="bg-white rounded-xl border p-5 card-lift"
                style="border-color:var(--line)">
                <div class="flex items-start justify-between mb-3">
                    <div class="text-[11px] font-medium uppercase tracking-widest font-mono" style="color:var(--ink-mute)">Cash</div>
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0"
                        style="background:rgba(16,185,129,0.08)">
                        <svg class="w-4 h-4 text-emerald-600" style="color:var(--success)" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                </div>
                <div class="text-2xl font-semibold leading-none mb-1 font-mono tabular-nums text-emerald-600" style="color:var(--success)">
                    Rp {{ number_format($saldoKas, 0, ',', '.') }}
                </div>
                <div class="text-xs text-emerald-700" style="color:var(--success)">Kas / laci saat ini</div>
            </div>

            {{-- 3. Transfer --}}
            <div class="bg-white rounded-xl border p-5 card-lift"
                style="border-color:var(--line)">
                <div class="flex items-start justify-between mb-3">
                    <div class="text-[11px] font-medium uppercase tracking-widest font-mono" style="color:var(--ink-mute)">Transfer</div>
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0"
                        style="background:rgba(99,102,241,0.08)">
                        <svg class="w-4 h-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                        </svg>
                    </div>
                </div>
                <div class="text-2xl font-semibold leading-none mb-1 font-mono tabular-nums text-indigo-600" style="color:#4F46E5">
                    Rp {{ number_format($saldoAtmLifetime, 0, ',', '.') }}
                </div>
                <div class="text-xs text-indigo-700" style="color:#4F46E5">Rekening / ATM saat ini</div>
            </div>

            {{-- 4. Pengeluaran --}}
            <div class="bg-white rounded-xl border p-5 card-lift"
                style="border-color:var(--line)">
                <div class="flex items-start justify-between mb-3">
                    <div class="text-[11px] font-medium uppercase tracking-widest font-mono" style="color:var(--ink-mute)">Pengeluaran</div>
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0"
                        style="background:rgba(239,68,68,0.08)">
                        <svg class="w-4 h-4 text-red-600" style="color:var(--warn)" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="text-2xl font-semibold leading-none mb-1 font-mono tabular-nums text-red-600" style="color:var(--warn)">
                    Rp {{ number_format($today['expenses'], 0, ',', '.') }}
                </div>
                <div class="text-xs" style="color:var(--ink-mute)">Operasional & biaya tanggal {{ $today['date_label'] }}</div>
            </div>

            {{-- 5. Hutang Hari Ini --}}
            <div class="bg-white rounded-xl border p-5 card-lift"
                style="border-color:var(--line)">
                <div class="flex items-start justify-between mb-3">
                    <div class="text-[11px] font-medium uppercase tracking-widest font-mono" style="color:var(--ink-mute)">Hutang Hari Ini</div>
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0"
                        style="background:rgba(245,158,11,0.08)">
                        <svg class="w-4 h-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                </div>
                <div class="text-2xl font-semibold leading-none mb-1 font-mono tabular-nums text-amber-600" style="color:#F59E0B">
                    Rp {{ number_format($today['debt'], 0, ',', '.') }}
                </div>
                <div class="text-xs" style="color:var(--ink-mute)">Piutang baru tanggal {{ $today['date_label'] }}</div>
            </div>

            {{-- 6. Laba Bersih --}}
            @if ($isSuperadmin)
            @php $isProfitNegative = $today['net_profit'] < 0; @endphp
            <div class="bg-white rounded-xl border p-5 card-lift"
                style="border-color:var(--line)">
                <div class="flex items-start justify-between mb-3">
                    <div class="text-[11px] font-medium uppercase tracking-widest font-mono" style="color:var(--ink-mute)">Laba Bersih</div>
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0"
                        style="background:{{ $isProfitNegative ? 'rgba(239,68,68,0.08)' : 'rgba(16,185,129,0.08)' }}">
                        <svg class="w-4 h-4 {{ $isProfitNegative ? 'text-red-600' : 'text-emerald-600' }}" style="color:{{ $isProfitNegative ? 'var(--warn)' : 'var(--success)' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                    </div>
                </div>
                <div class="text-2xl font-semibold leading-none mb-1 font-mono tabular-nums {{ $isProfitNegative ? 'text-red-600' : 'text-emerald-600' }}" style="color:{{ $isProfitNegative ? 'var(--warn)' : 'var(--success)' }}">
                    Rp {{ number_format($today['net_profit'], 0, ',', '.') }}
                </div>
                <div class="text-xs" style="color:var(--ink-mute)">Penjualan dikurangi biaya</div>
            </div>
            @endif

        </div>

        {{-- Capitals and Expenses for Superadmin and Admin --}}
        @if (in_array(auth()->user()->role->value, ['superadmin', 'admin']))
            <div class="space-y-6 mt-6">

                {{-- Operational Expenses Log --}}
                <div class="bg-white rounded-xl border overflow-hidden shadow-sm" style="border-color:var(--line)">
                    <div class="px-5 py-4 border-b flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4" style="border-color:var(--line)">
                        <div>
                            <h3 class="text-sm font-semibold" style="color:var(--ink)">Biaya Pengeluaran &amp; Pembelian Stok</h3>
                            <p class="text-[11px] mt-0.5" style="color:var(--ink-mute)">Log biaya pengeluaran operasional
                                dan pembelian stok HP toko yang mengurangi kas/ATM</p>
                        </div>
 
                        <div class="flex items-center gap-3">
                            {{-- Type Filter Segmented Control --}}
                            <div class="flex items-center gap-1 bg-gray-100 p-1 rounded-lg text-xs font-semibold"
                                style="height: 36px;">
                                @foreach (['all' => 'Semua', 'income' => 'Pemasukan', 'expense' => 'Pengeluaran'] as $tf => $tfLbl)
                                    @php $isTfActive = request('type_filter', 'all') === $tf; @endphp
                                    <button type="button" onclick="setTypeFilter('{{ $tf }}')"
                                        class="px-3 rounded-md transition-all text-[11px] h-7 flex items-center justify-center {{ $isTfActive ? 'bg-white text-blue-600 font-bold shadow-sm' : 'text-gray-500 hover:text-gray-800' }}">
                                        {{ $tfLbl }}
                                    </button>
                                @endforeach
                            </div>

                            @if (!$isLocked)
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
                            @else
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-gray-100 text-gray-400">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                    Terkunci
                                </span>
                            @endif
                        </div>
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
                                        @if ($expense->category === 'gaji' && !$isSuperadmin) @continue @endif
                                        <tr style="border-bottom:1px solid var(--line)">
                                            <td class="px-5 py-3 font-mono" style="color:var(--ink-soft)">
                                                {{ $expense->expense_date->format('d/m/Y') }}</td>
                                            <td class="px-5 py-3 font-medium" style="color:var(--ink)">
                                                {{ $expense->description }}</td>
                                            <td class="px-5 py-3">
                                                @if ($expense->is_virtual_sale ?? false)
                                                    <span class="px-2 py-0.5 rounded bg-green-100 text-green-800 font-mono text-[9px] font-semibold">
                                                        Penjualan
                                                    </span>
                                                @elseif ($expense->is_virtual_debt_payment ?? false)
                                                    <span class="px-2 py-0.5 rounded bg-amber-100 text-amber-800 font-mono text-[9px] font-semibold">
                                                        Pelunasan
                                                    </span>
                                                @elseif ($expense->category === 'stok_hp')
                                                    <span class="px-2 py-0.5 rounded bg-blue-100 text-blue-800 font-mono text-[9px] font-semibold">
                                                        Stok HP
                                                    </span>
                                                @else
                                                    <span class="px-2 py-0.5 rounded bg-gray-100 text-gray-700 font-mono text-[9px]">
                                                        {{ $expense->category === 'tarik_owner' ? 'Tarik Saldo Owner' : ($expense->category === 'listrik' ? 'Listrik & Gas' : ucwords($expense->category)) }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-5 py-3">
                                                @if (($expense->payment_method ?? 'cash') === 'transfer')
                                                    <span class="px-2 py-0.5 rounded-full bg-blue-50 text-blue-600 font-mono text-[9px] font-semibold">Transfer</span>
                                                @elseif (($expense->payment_method ?? 'cash') === 'cash')
                                                    <span class="px-2 py-0.5 rounded-full bg-green-50 text-green-600 font-mono text-[9px] font-semibold">Tunai</span>
                                                @elseif (($expense->payment_method ?? 'cash') === 'utang')
                                                    <span class="px-2 py-0.5 rounded-full bg-red-50 text-red-600 font-mono text-[9px] font-semibold">Utang</span>
                                                @else
                                                    <span class="px-2 py-0.5 rounded-full bg-amber-50 text-amber-600 font-mono text-[9px] font-semibold">Split</span>
                                                @endif
                                            </td>
                                            <td class="px-5 py-3" style="color:var(--ink-mute)">{{ $expense->notes ?: '—' }}
                                            </td>
                                            @if (($expense->is_virtual_sale ?? false) || ($expense->is_virtual_debt_payment ?? false))
                                                <td class="px-5 py-3 text-right font-mono font-bold tabular-nums text-emerald-600"
                                                    style="color:var(--success)">
                                                    +Rp {{ number_format($expense->amount, 0, ',', '.') }}
                                                </td>
                                            @else
                                                <td class="px-5 py-3 text-right font-mono font-bold tabular-nums text-red-600"
                                                    style="color:var(--warn)">
                                                    -Rp {{ number_format($expense->amount, 0, ',', '.') }}
                                                </td>
                                            @endif
                                            <td class="px-5 py-3 text-center">
                                                @if ($expense->is_virtual_sale ?? false)
                                                    <a href="{{ route('sales.show', $expense->sale_id) }}"
                                                        class="inline-flex items-center justify-center px-2 py-1 rounded-lg transition-colors text-[10px] font-semibold"
                                                        style="background:#E6F4EA;color:var(--success)"
                                                        onmouseenter="this.style.background='#D2EBD8'"
                                                        onmouseleave="this.style.background='#E6F4EA'">
                                                        Lihat Detail
                                                    </a>
                                                @elseif ($expense->is_virtual_debt_payment ?? false)
                                                    <a href="{{ route('sales.show', $expense->sale_id) }}"
                                                        class="inline-flex items-center justify-center px-2 py-1 rounded-lg transition-colors text-[10px] font-semibold"
                                                        style="background:#FFF8E1;color:#B45309"
                                                        onmouseenter="this.style.background='#FDE68A'"
                                                        onmouseleave="this.style.background='#FFF8E1'">
                                                        Lihat Detail
                                                    </a>
                                                @elseif ($expense->is_virtual ?? false)
                                                    <a href="{{ route('units.show', $expense->unit_id) }}"
                                                        class="inline-flex items-center justify-center px-2 py-1 rounded-lg transition-colors text-[10px] font-semibold"
                                                        style="background:#EFF6FF;color:var(--accent)"
                                                        onmouseenter="this.style.background='#DBEAFE'"
                                                        onmouseleave="this.style.background='#EFF6FF'">
                                                        Lihat Unit
                                                    </a>
                                                @else
                                                    @if (!$isLocked)
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
                                                    @else
                                                        <span class="text-xs text-gray-400 italic font-medium">Terkunci</span>
                                                    @endif
                                                @endif
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
                                        @if (auth()->user()->isSuperAdmin())
                                            <option value="gaji">Gaji</option>
                                        @endif
                                        <option value="sewa">Sewa</option>
                                        <option value="lainnya">Lainnya</option>
                                        @if (auth()->user()->isSuperAdmin())
                                            <option value="tarik_owner">Tarik Saldo Owner</option>
                                        @endif
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
                        closeClosingModal();
                    }
                });

                function openClosingModal(date) {
                    const modal = document.getElementById('modal-tutup-buku');
                    const loading = document.getElementById('closing-modal-loading');
                    const form = document.getElementById('closing-form');
                    
                    if (!modal) return;
                    modal.classList.remove('hidden');
                    document.body.style.overflow = 'hidden';
                    
                    loading.classList.remove('hidden');
                    form.classList.add('hidden');
                    
                    // Fetch date metrics
                    const closingDataUrl = @json(route('daily-closings.data'));
                    const setClosingText = (id, value) => {
                        const el = document.getElementById(id);
                        if (el) el.innerText = value;
                    };

                    fetch(`${closingDataUrl}?date=${encodeURIComponent(date)}`, {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                        })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('HTTP ' + response.status);
                            }
                            return response.json();
                        })
                        .then(data => {
                            document.getElementById('closing-date-input').value = date;
                            
                            // Format date nicely
                            const dateObj = new Date(date);
                            const formattedDate = dateObj.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
                            document.getElementById('closing-date-display').innerText = formattedDate;
                            
                            const formatRupiah = (val) => 'Rp ' + Math.round(val).toLocaleString('id-ID');
                            
                            setClosingText('closing-total-income', formatRupiah(data.total_income));
                            setClosingText('closing-gas-income', formatRupiah(data.gas_income));
                            setClosingText('closing-hp-purchase', formatRupiah(data.hp_purchase));
                            setClosingText('closing-hp-sale', formatRupiah(data.hp_sale));
                            setClosingText('closing-laba', formatRupiah(data.laba));
                            setClosingText('closing-cash-system', formatRupiah(data.cash_system));
                            setClosingText('closing-cash-system-card', formatRupiah(data.cash_system));
                            setClosingText('closing-atm-system', formatRupiah(data.atm_system));
                            setClosingText('closing-atm-system-card', formatRupiah(data.atm_system));
                            setClosingText('closing-transfer-income', formatRupiah(data.transfer_income));
                            setClosingText('closing-debt-amount', formatRupiah(data.debt_amount));
                            
                            // If physical cash was already submitted previously (draft / edit)
                            const cashPhysicalInput = document.getElementById('closing-cash-physical');
                            if (cashPhysicalInput) {
                                cashPhysicalInput.value = data.cash_physical > 0 ? Math.round(data.cash_physical).toLocaleString('id-ID') : '';
                            
                                // Trigger input event to format if it has value
                                if (cashPhysicalInput.value) {
                                    const event = new Event('input', { bubbles: true });
                                    cashPhysicalInput.dispatchEvent(event);
                                }
                            }

                            // If physical ATM was already submitted previously (draft / edit)
                            const atmPhysicalInput = document.getElementById('closing-atm-physical');
                            if (atmPhysicalInput) {
                                atmPhysicalInput.value = data.atm_physical > 0 ? Math.round(data.atm_physical).toLocaleString('id-ID') : '';
                            
                                // Trigger input event to format if it has value
                                if (atmPhysicalInput.value) {
                                    const event = new Event('input', { bubbles: true });
                                    atmPhysicalInput.dispatchEvent(event);
                                }
                            }
                            
                            const notesTextarea = form.querySelector('textarea[name="notes"]');
                            if (notesTextarea) notesTextarea.value = data.notes || '';
                            
                            loading.classList.add('hidden');
                            form.classList.remove('hidden');
                            const firstInput = cashPhysicalInput || notesTextarea;
                            if (firstInput) setTimeout(() => firstInput.focus(), 100);
                        })
                        .catch(err => {
                            alert('Gagal mengambil data keuangan harian. Silakan muat ulang halaman dan coba lagi.');
                            closeClosingModal();
                        });
                }

                function closeClosingModal() {
                    const modal = document.getElementById('modal-tutup-buku');
                    if (modal) {
                        modal.classList.add('hidden');
                        document.body.style.overflow = '';
                    }
                }
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
                                    @if (auth()->user()->isSuperAdmin())
                                        <option value="gaji">Gaji</option>
                                    @endif
                                    <option value="sewa">Sewa</option>
                                    <option value="lainnya">Lainnya</option>
                                    @if (auth()->user()->isSuperAdmin())
                                        <option value="tarik_owner">Tarik Saldo Owner</option>
                                    @endif
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

        {{-- ========== MODAL: Tutup Buku ========== --}}
        <div id="modal-tutup-buku" class="fixed inset-0 z-[120] hidden overflow-y-auto"
            onclick="if(event.target===this){closeClosingModal()}">
            <div class="fixed inset-0" style="background:rgba(10,37,64,.5)"></div>
            <div class="relative min-h-full flex items-center justify-center px-4 pt-12 pb-12">
                <div class="w-full max-w-2xl bg-white rounded-2xl shadow-2xl overflow-hidden modal-pop animate-fade-in"
                    onclick="event.stopPropagation()">
                    
                    {{-- Modal Header --}}
                    <div class="flex items-start justify-between px-6 py-5 border-b" style="border-color:var(--line)">
                        <div class="flex items-center gap-3">
                            <span class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 bg-blue-50 text-blue-600">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </span>
                            <div>
                                <h3 class="text-base font-semibold leading-none text-gray-900">Tutup Buku Harian</h3>
                                <p class="text-xs text-gray-500 mt-1.5">Kunci transaksi & hitung realisasi kas fisik harian</p>
                            </div>
                        </div>
                        <button onclick="closeClosingModal()"
                            class="w-8 h-8 flex items-center justify-center rounded-lg transition-colors text-gray-400 hover:text-gray-600 bg-gray-50 hover:bg-gray-100">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    {{-- Loading State --}}
                    <div id="closing-modal-loading" class="p-12 flex flex-col items-center justify-center space-y-3">
                        <svg class="animate-spin h-8 w-8 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <p class="text-xs text-gray-500 font-medium">Memuat data keuangan harian...</p>
                    </div>

                    {{-- Form --}}
                    <form id="closing-form" method="POST" action="{{ route('daily-closings.store') }}" class="p-6 space-y-6 hidden">
                        @csrf
                        <input type="hidden" name="closing_date" id="closing-date-input" />

                        <div class="bg-gray-50 rounded-xl p-4 grid grid-cols-2 {{ $isSuperadmin ? 'md:grid-cols-4' : 'md:grid-cols-3' }} gap-4 text-xs">
                            <div>
                                <div class="text-gray-500 font-semibold mb-0.5">Tanggal Buku</div>
                                <div class="font-bold text-gray-800" id="closing-date-display">-</div>
                            </div>
                            <div>
                                <div class="text-gray-500 font-semibold mb-0.5">Total Uang Masuk</div>
                                <div class="font-bold text-blue-600" id="closing-total-income">-</div>
                            </div>
                            <div>
                                <div class="text-gray-500 font-semibold mb-0.5">Penjualan HP</div>
                                <div class="font-bold text-gray-800" id="closing-hp-sale">-</div>
                            </div>
                            @if ($isSuperadmin)
                                <div>
                                    <div class="text-gray-500 font-semibold mb-0.5">Laba Bersih</div>
                                    <div class="font-bold text-emerald-600" id="closing-laba">-</div>
                                </div>
                            @endif
                        </div>

                        <div class="grid grid-cols-1 {{ $isSuperadmin ? 'md:grid-cols-2' : '' }} gap-6">
                            {{-- Metrics Details --}}
                            <div class="space-y-3">
                                <h4 class="text-xs font-bold text-gray-700 uppercase tracking-wider font-mono">Detail Transaksi Sistem</h4>
                                <div class="border rounded-xl divide-y text-xs">
                                    <div class="flex justify-between p-2.5 bg-white">
                                        <span class="text-gray-600">Pendapatan Gas</span>
                                        <span class="font-semibold text-gray-800" id="closing-gas-income">-</span>
                                    </div>
                                    @if ($isSuperadmin)
                                    <div class="flex justify-between p-2.5 bg-white">
                                        <span class="text-gray-600">Pembelian HP (Stok)</span>
                                        <span class="font-semibold text-red-600" id="closing-hp-purchase">-</span>
                                    </div>
                                    @endif
                                    <div class="flex justify-between p-2.5 bg-white">
                                        <span class="text-gray-600">Transfer Masuk</span>
                                        <span class="font-semibold text-gray-800" id="closing-transfer-income">-</span>
                                    </div>
                                    <div class="flex justify-between p-2.5 bg-white">
                                        <span class="text-gray-600">Piutang / Utang</span>
                                        <span class="font-semibold text-amber-600" id="closing-debt-amount">-</span>
                                    </div>
                                    @unless ($isSuperadmin)
                                        <div class="flex justify-between p-2.5 bg-blue-50">
                                            <span class="text-blue-800 font-semibold">Kas Sistem Harian</span>
                                            <span class="font-semibold text-blue-700 font-mono" id="closing-cash-system">-</span>
                                        </div>
                                        <div class="flex justify-between p-2.5 bg-purple-50">
                                            <span class="text-purple-800 font-semibold">ATM Sistem Harian</span>
                                            <span class="font-semibold text-purple-700 font-mono" id="closing-atm-system">-</span>
                                        </div>
                                    @endunless
                                </div>
                            </div>

                            {{-- Reconciliation (superadmin only) --}}
                            @if ($isSuperadmin)
                            <div class="space-y-4">
                                <h4 class="text-xs font-bold text-gray-700 uppercase tracking-wider font-mono">Reorganisasi Kas & ATM</h4>

                                <!-- KAS TUNAI -->
                                <div class="space-y-2 pb-3 border-b" style="border-color:var(--line)">
                                    <div class="bg-blue-50 border border-blue-200 rounded-xl p-3 flex justify-between items-center text-xs">
                                        <div>
                                            <span class="text-blue-800 font-semibold block">Kas Sistem</span>
                                            <span class="text-[10px] text-blue-600 block mt-0.5">Uang masuk tunai - biaya tunai</span>
                                        </div>
                                        <span class="text-sm font-bold text-blue-700 font-mono" id="closing-cash-system-card">-</span>
                                    </div>

                                    <div>
                                        <label class="field-label text-[11px] font-semibold">Realisasi Uang Cash Fisik <span style="color:var(--warn)">*</span></label>
                                        <div class="money-wrap mt-1">
                                            <span class="rp-prefix">Rp</span>
                                            <input type="text" name="cash_physical" id="closing-cash-physical" required placeholder="0"
                                                class="field-input money-input font-mono font-bold" inputmode="numeric"
                                                style="height:40px;font-size:14px" />
                                        </div>
                                    </div>
                                </div>

                                <!-- ATM / TRANSFER -->
                                <div class="space-y-2">
                                    <div class="bg-purple-50 border border-purple-200 rounded-xl p-3 flex justify-between items-center text-xs">
                                        <div>
                                            <span class="text-purple-800 font-semibold block">ATM Sistem</span>
                                            <span class="text-[10px] text-purple-600 block mt-0.5">Uang masuk transfer - biaya transfer</span>
                                        </div>
                                        <span class="text-sm font-bold text-purple-700 font-mono" id="closing-atm-system-card">-</span>
                                    </div>

                                    <div>
                                        <label class="field-label text-[11px] font-semibold">Realisasi Saldo ATM Fisik <span style="color:var(--warn)">*</span></label>
                                        <div class="money-wrap mt-1">
                                            <span class="rp-prefix">Rp</span>
                                            <input type="text" name="atm_physical" id="closing-atm-physical" required placeholder="0"
                                                class="field-input money-input font-mono font-bold" inputmode="numeric"
                                                style="height:40px;font-size:14px" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @else
                            <input type="hidden" name="cash_physical" value="0" />
                            <input type="hidden" name="atm_physical" value="0" />
                            @endif
                        </div>

                        <div>
                            <label class="field-label">Catatan Penutupan Buku</label>
                            <textarea name="notes" rows="2" class="field-input mt-1" placeholder="Tuliskan catatan rekonsiliasi atau catatan selisih jika ada..."></textarea>
                        </div>

                        <div class="flex gap-3 pt-2 border-t" style="border-color:var(--line)">
                            <button type="submit" class="btn-primary flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold">
                                Konfirmasi &amp; Tutup Buku
                            </button>
                            <button type="button" onclick="closeClosingModal()" class="btn-secondary px-6">
                                Batal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        @endif
    </div>
@endsection
